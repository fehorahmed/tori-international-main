<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)
            ->orWhere('username', $this->email)
            ->orWhere('mobile_no', $this->email)
            ->first();
        if (!$user){
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
        if ($user->status == 0){
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'status' => trans('auth.deactivated'),
            ]);
        }

        // === CASE 1: OTP Login (6 digits) ===
        if (strlen($this->password) === 6) {
            if (!$user->otp || !$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'password' => 'OTP is invalid or expired.',
                ]);
            }

            if ($this->password != $user->otp) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'password' => 'Invalid OTP.',
                ]);
            }

            // OTP is valid → log in
            Auth::login($user, false);

            // Store session expiration for OTP (10 minutes)
            $this->session()->put('otp_login', true);
            $this->session()->put('session_expire_at', now()->addMinutes(10));

            RateLimiter::clear($this->throttleKey());
            return;
        }
        // === CASE 2: Password Login (> 6) ===
        if (!Hash::check($this->password, $user->password)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'password' => trans('auth.password'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        $this->session()->put('otp_login', false);

        RateLimiter::clear($this->throttleKey());

//        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
//            RateLimiter::hit($this->throttleKey());
//
//            throw ValidationException::withMessages([
//                'email' => trans('auth.failed'),
//            ]);
//        }

    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
