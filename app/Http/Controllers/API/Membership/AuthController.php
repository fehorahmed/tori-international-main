<?php

namespace App\Http\Controllers\API\Membership;

use App\Enumeration\SupplierType;
use App\Http\Controllers\Controller;
use App\Models\CustomerDevice;
use App\Models\CustomerOtpLog;
use App\Models\Supplier;
use App\Services\SmsManagement\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Step 1: Initiate Login / Send OTP
     * Endpoint: POST /api/membership/v1/auth/initiate
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'mobile_no' => 'required|string',
            'device_id' => 'nullable|string|max:128',
            'device_token' => 'nullable|string|max:256',
            'reset_pin' => 'sometimes|boolean',
        ]);

        $mobileNo = preg_replace('/[^0-9]/', '', $request->mobile_no);
        // Normalize BD mobile numbers:
        // Case 1: with country code '8801...' -> '01...'
        if (str_starts_with($mobileNo, '880')) {
            $mobileNo = '0'.substr($mobileNo, 3);
        }
        // Case 2: 10 digits without leading zero '17...' or '14...' -> '01...'
        elseif (strlen($mobileNo) === 10 && str_starts_with($mobileNo, '1')) {
            $mobileNo = '0'.$mobileNo;
        }

        $customer = Supplier::where(function ($q) use ($mobileNo) {
            $q->where('mobile_no', $mobileNo)
                ->orWhere('mobile_no', '88'.$mobileNo)
                ->orWhere('mobile_no', ltrim($mobileNo, '0'));
        })->where('type', '!=', SupplierType::$SUPPLIER)->first();

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => $this->message('No account was found for this mobile number. Please contact your nearest branch.', 'এই মোবাইল নম্বরে কোনো অ্যাকাউন্ট পাওয়া যায়নি। অনুগ্রহ করে নিকটস্থ শাখায় যোগাযোগ করুন।'),
            ], 404);
        }

        if ($customer->status != 1) {
            return response()->json([
                'success' => false,
                'message' => $this->message('Your account is inactive. Please contact the administrator.', 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।'),
            ], 403);
        }

        if (! $request->boolean('reset_pin') && ! empty($customer->pin) && $this->trustedDevice($customer, $request)) {
            return response()->json(['success' => true,
                'message' => $this->message('Enter your PIN to sign in.', 'লগইন করতে আপনার পিন দিন।'),
                'data' => ['mobile_no' => $mobileNo, 'otp_required' => false, 'has_pin' => true]]);
        }

        // Generate 6-Digit OTP
        // For development/testing: fallback to fixed '123456' if environment is local, or generate random
        $otp = app()->environment('local') ? '123456' : (string) random_int(100000, 999999);

        // Save OTP to Cache for 3 minutes (180 seconds)
        $otpCacheKey = $this->otpKey($mobileNo, $request->device_id);
        Cache::put($otpCacheKey, [
            'otp' => Hash::make($otp),
            'customer_id' => $customer->id,
            'device_id' => $request->device_id,
            'reset_pin' => $request->boolean('reset_pin'),
            'attempts' => 0,
        ], now()->addMinutes(3));

        // Audit Log: Customer OTP Log
        CustomerOtpLog::create([
            'customer_id' => $customer->id,
            'mobile_no' => $mobileNo,
            'otp' => app()->environment('local') ? $otp : '******',
            'purpose' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(3),
        ]);

        // Send SMS via SmsService (only if not testing or gateway available)
        try {
            if (! app()->environment(['local', 'testing'])) {
                $smsService = app(SmsService::class);
                $smsMessage = "আপনার তরি মেম্বারশিপ লগইন ওটিপি হলো: {$otp}। এটি ৩ মিনিট কার্যকর থাকবে।";
                $smsService->sendSms($mobileNo, $smsMessage);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP to {$mobileNo}: ".$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $this->message('A 6-digit OTP has been sent to your mobile number.', 'আপনার মোবাইল নম্বরে একটি ৬ ডিজিটের ওটিপি পাঠানো হয়েছে।'),
            'data' => [
                'mobile_no' => $mobileNo,
                'otp_required' => true,
                'otp_expires_in_seconds' => 180,
                'resend_wait_seconds' => 60,
                'has_pin' => ! empty($customer->pin),
                'is_biometric_enabled' => (bool) $customer->is_biometric_enabled,
                // In local development, return OTP for easy testing:
                'debug_otp' => app()->environment('local') ? $otp : null,
            ],
        ]);
    }

    /**
     * Step 2: Verify OTP
     * Endpoint: POST /api/membership/v1/auth/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile_no' => 'required|string',
            'otp' => 'required|digits:6',
            'device_id' => 'nullable|string|max:128',
        ]);

        $mobileNo = preg_replace('/[^0-9]/', '', $request->mobile_no);
        if (str_starts_with($mobileNo, '880')) {
            $mobileNo = '0'.substr($mobileNo, 3);
        } elseif (strlen($mobileNo) === 10 && str_starts_with($mobileNo, '1')) {
            $mobileNo = '0'.$mobileNo;
        }

        $otpCacheKey = $this->otpKey($mobileNo, $request->device_id);
        $cachedData = Cache::get($otpCacheKey);

        if (! $cachedData) {
            return response()->json([
                'success' => false,
                'message' => $this->message('Your OTP has expired. Please request a new code.', 'ওটিপির মেয়াদ শেষ হয়ে গেছে। অনুগ্রহ করে পুনরায় ওটিপি পাঠান।'),
            ], 422);
        }

        // Limit invalid OTP attempts (max 5)
        if ($cachedData['attempts'] >= 5) {
            Cache::forget($otpCacheKey);

            return response()->json([
                'success' => false,
                'message' => $this->message('Too many incorrect OTP attempts. Please start again.', 'অতিরিক্ত ভুল ওটিপি দেওয়ার কারণে সেশনটি বাতিল করা হয়েছে। নতুন করে চেষ্টা করুন।'),
            ], 429);
        }

        if (! Hash::check($request->otp, $cachedData['otp'])) {
            $cachedData['attempts'] += 1;
            Cache::put($otpCacheKey, $cachedData, now()->addMinutes(3));

            return response()->json([
                'success' => false,
                'message' => $this->message('Incorrect OTP. Please try again.', 'ভুল ওটিপি দিয়েছেন। আবার চেষ্টা করুন।'),
            ], 422);
        }

        // OTP is valid -> delete OTP cache
        Cache::forget($otpCacheKey);

        $customer = Supplier::find($cachedData['customer_id']);
        abort_unless($customer && $customer->status == 1 && $customer->type !== SupplierType::$SUPPLIER, 403);

        // Generate a short-lived verification token (valid for 15 minutes)
        $verificationToken = Str::random(64);
        Cache::put("verified_token_{$verificationToken}", [
            'customer_id' => $customer->id,
            'mobile_no' => $mobileNo,
            'device_id' => $cachedData['device_id'] ?? null,
            'reset_pin' => $cachedData['reset_pin'] ?? false,
        ], now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'message' => $this->message('OTP verified successfully.', 'ওটিপি সফলভাবে যাচাই করা হয়েছে।'),
            'data' => [
                'mobile_no' => $mobileNo,
                'has_pin' => ! ($cachedData['reset_pin'] ?? false) && ! empty($customer->pin),
                'verification_token' => $verificationToken,
            ],
        ]);
    }

    /**
     * Step 3 (A): Login with 6-Digit PIN
     * Endpoint: POST /api/membership/v1/auth/login-pin
     */
    public function loginWithPin(Request $request)
    {
        $request->validate([
            'mobile_no' => 'required|string', 'pin' => 'required|digits:6',
            'verification_token' => 'nullable|string', 'device_id' => 'nullable|string|max:128',
            'device_token' => 'nullable|string|max:256', 'device_name' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:32',
        ]);
        $mobile = $this->normalizeMobile($request->mobile_no);
        $verification = $request->verification_token ? Cache::get("verified_token_{$request->verification_token}") : null;
        $customer = $verification ? Supplier::find($verification['customer_id']) : $this->customerByMobile($mobile);
        if (! $customer || $customer->status != 1 || $customer->type === SupplierType::$SUPPLIER) {
            return response()->json(['success' => false, 'message' => $this->message('Unable to sign in with these details.', 'এই তথ্য দিয়ে লগইন করা যায়নি।')], 401);
        }
        $verified = $request->verification_token
            ? $verification && ($verification['mobile_no'] ?? '') === $mobile && ($verification['device_id'] ?? null) === $request->device_id
            : (bool) $this->trustedDevice($customer, $request);
        if (! $verified) {
            return response()->json(['success' => false, 'otp_required' => true,
                'message' => $this->message('Please verify this device with an OTP first.', 'আগে ওটিপি দিয়ে এই ডিভাইসটি যাচাই করুন।')], 401);
        }
        if (empty($customer->pin) || ! Hash::check($request->pin, $customer->pin)) {
            return response()->json(['success' => false, 'message' => $this->message('Incorrect PIN.', 'ভুল পিন দিয়েছেন।')], 401);
        }
        $deviceToken = $verification ? $this->rememberDevice($customer, $request) : null;
        if (! $verification) {
            $this->trustedDevice($customer, $request)?->update(['last_active_at' => now()]);
        }
        $token = $customer->createToken($request->device_name ?: 'Mobile App')->plainTextToken;
        if ($verification) {
            Cache::forget("verified_token_{$request->verification_token}");
        }

        return response()->json(['success' => true, 'message' => $this->message('Login successful.', 'লগইন সফল হয়েছে।'),
            'data' => ['token' => $token, 'device_token' => $deviceToken, 'customer' => $this->formatCustomerProfile($customer)]]);
    }

    /**
     * Step 3 (B): Set / Reset 6-Digit PIN
     * Endpoint: POST /api/membership/v1/auth/set-pin
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'verification_token' => 'required|string',
            'pin' => 'required|digits:6|confirmed', // expects 'pin' and 'pin_confirmation'
            'device_name' => 'nullable|string|max:255',
            'device_id' => 'nullable|string|max:128',
            'platform' => 'nullable|string|max:32',
        ]);

        $tokenData = Cache::get("verified_token_{$request->verification_token}");
        if (! $tokenData || ($tokenData['device_id'] ?? null) !== $request->device_id) {
            return response()->json([
                'success' => false,
                'message' => $this->message('Your verification token has expired.', 'ভেরিফিকেশন টোকেনটির মেয়াদ শেষ হয়ে গেছে।'),
            ], 401);
        }

        $customer = Supplier::find($tokenData['customer_id']);
        if (! $customer || $customer->status != 1 || $customer->type === SupplierType::$SUPPLIER) {
            return response()->json([
                'success' => false,
                'message' => $this->message('Customer not found.', 'কাস্টমার খুঁজে পাওয়া যায়নি।'),
            ], 404);
        }

        CustomerDevice::where('customer_id', $customer->id)->update(['trusted_token_hash' => null]);
        $deviceToken = $this->rememberDevice($customer, $request);

        // Save hashed PIN
        $customer->pin = Hash::make($request->pin);
        $customer->save();

        Cache::forget("verified_token_{$request->verification_token}");

        $deviceName = $request->device_name ?: 'Mobile App';
        $token = $customer->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $this->message('Your 6-digit PIN has been saved successfully.', 'আপনার ৬ ডিজিটের পিন সফলভাবে সংরক্ষিত হয়েছে।'),
            'data' => [
                'token' => $token,
                'device_token' => $deviceToken,
                'customer' => $this->formatCustomerProfile($customer),
            ],
        ]);
    }

    /**
     * Biometric Login (Fingerprint / FaceID)
     * Endpoint: POST /api/membership/v1/auth/login-biometric
     */
    public function loginBiometric(Request $request)
    {
        $request->validate([
            'mobile_no' => 'required|string',
            'biometric_token' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $mobileNo = preg_replace('/[^0-9]/', '', $request->mobile_no);
        if (str_starts_with($mobileNo, '880')) {
            $mobileNo = '0'.substr($mobileNo, 3);
        } elseif (strlen($mobileNo) === 10 && str_starts_with($mobileNo, '1')) {
            $mobileNo = '0'.$mobileNo;
        }

        $customer = Supplier::where(function ($q) use ($mobileNo) {
            $q->where('mobile_no', $mobileNo)
                ->orWhere('mobile_no', '88'.$mobileNo);
        })->where('type', '!=', SupplierType::$SUPPLIER)->first();

        if (! $customer || ! $customer->is_biometric_enabled || empty($customer->biometric_token)) {
            return response()->json([
                'success' => false,
                'message' => $this->message('Biometric login is not enabled.', 'বায়োমেট্রিক লগইন সক্রিয় নেই।'),
            ], 403);
        }

        if (! Hash::check($request->biometric_token, $customer->biometric_token)) {
            return response()->json([
                'success' => false,
                'message' => $this->message('Biometric authentication failed.', 'বায়োমেট্রিক অথেন্টিকেশন ব্যর্থ হয়েছে।'),
            ], 401);
        }

        $deviceName = $request->device_name ?: 'Biometric Device';
        $token = $customer->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $this->message('Biometric login successful.', 'বায়োমেট্রিক লগইন সফল হয়েছে।'),
            'data' => [
                'token' => $token,
                'customer' => $this->formatCustomerProfile($customer),
            ],
        ]);
    }

    /**
     * Enable/Disable Biometrics
     * Endpoint: POST /api/membership/v1/auth/toggle-biometric (auth:sanctum)
     */
    public function toggleBiometric(Request $request)
    {
        $request->validate([
            'enable' => 'required|boolean',
            'biometric_token' => 'nullable|string|required_if:enable,true',
        ]);

        $customer = $request->user();

        if ($request->enable) {
            $customer->is_biometric_enabled = 1;
            $customer->biometric_token = Hash::make($request->biometric_token);
        } else {
            $customer->is_biometric_enabled = 0;
            $customer->biometric_token = null;
        }

        $customer->save();

        return response()->json([
            'success' => true,
            'message' => $request->enable ? $this->message('Biometric login has been enabled.', 'বায়োমেট্রিক লগইন চালু করা হয়েছে।') : $this->message('Biometric login has been disabled.', 'বায়োমেট্রিক লগইন বন্ধ করা হয়েছে।'),
        ]);
    }

    /**
     * Logout
     * Endpoint: POST /api/membership/v1/auth/logout (auth:sanctum)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => $this->message('Logout successful.', 'লগআউট সফল হয়েছে।'),
        ]);
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (str_starts_with($mobile, '880')) {
            return '0'.substr($mobile, 3);
        }
        if (strlen($mobile) === 10 && str_starts_with($mobile, '1')) {
            return '0'.$mobile;
        }

        return $mobile;
    }

    private function customerByMobile(string $mobile): ?Supplier
    {
        return Supplier::whereIn('mobile_no', [$mobile, '88'.$mobile, ltrim($mobile, '0')])
            ->where('type', '!=', SupplierType::$SUPPLIER)->first();
    }

    private function otpKey(string $mobile, ?string $device): string
    {
        return "customer_otp_{$mobile}".($device ? ':'.hash('sha256', $device) : '');
    }

    private function trustedDevice(Supplier $customer, Request $request): ?CustomerDevice
    {
        if (! $request->device_id || ! $request->device_token) {
            return null;
        }

        return CustomerDevice::where('customer_id', $customer->id)->where('device_id', $request->device_id)
            ->where('trusted_token_hash', hash('sha256', $request->device_token))->first();
    }

    private function rememberDevice(Supplier $customer, Request $request): ?string
    {
        if (! $request->device_id) {
            return null;
        }
        $secret = Str::random(64);
        CustomerDevice::updateOrCreate(['customer_id' => $customer->id, 'device_id' => $request->device_id], [
            'device_name' => $request->device_name ?: 'Mobile App', 'platform' => $request->platform,
            'trusted_token_hash' => hash('sha256', $secret), 'last_active_at' => now(),
        ]);

        return $secret;
    }

    /** Honor the app language; retain Bengali for existing clients without a header. */
    private function message(string $english, string $bengali): string
    {
        return request()->hasHeader('Accept-Language')
            && request()->getPreferredLanguage(['bn', 'en']) === 'en'
            ? $english
            : $bengali;
    }

    /**
     * Helper to format customer profile output
     */
    private function formatCustomerProfile($customer)
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'member_no' => $customer->member_no,
            'mobile_no' => $customer->mobile_no,
            'email' => $customer->email,
            'image' => $customer->image ? asset($customer->image) : null,
            'is_biometric_enabled' => (bool) $customer->is_biometric_enabled,
        ];
    }
}
