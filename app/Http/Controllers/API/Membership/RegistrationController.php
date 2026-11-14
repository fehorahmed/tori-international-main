<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;
use App\Models\CustomerDevice;
use App\Models\Supplier;
use App\Services\SmsManagement\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    private function message(Request $r, string $en, string $bn): string
    {
        return KycController::message($r, $en, $bn);
    }

    private function fail(Request $r, string $en, string $bn, string $field = 'registration'): never
    {
        throw ValidationException::withMessages([$field => [$this->message($r, $en, $bn)]]);
    }

    private function mobile(string $value): string
    {
        $digits = preg_replace('/[^0-9]/', '', $value);
        if (str_starts_with($digits, '880')) return '0'.substr($digits, 3);
        return strlen($digits) === 10 && str_starts_with($digits, '1') ? '0'.$digits : $digits;
    }

    private function ensureNew(Request $r, string $mobile, ?string $email): void
    {
        if (Supplier::withTrashed()->whereIn('mobile_no', [$mobile, substr($mobile, 1), '88'.$mobile, '+88'.$mobile])->exists()) {
            $this->fail($r, 'This mobile number already has an account. Please sign in or contact your branch.', 'এই মোবাইল নম্বরে অ্যাকাউন্ট আছে। লগইন করুন অথবা শাখায় যোগাযোগ করুন।', 'mobile_no');
        }
        if ($email && Supplier::withTrashed()->where('email', $email)->exists()) {
            $this->fail($r, 'This email address is already in use.', 'এই ইমেইল ঠিকানা ইতিমধ্যে ব্যবহৃত হচ্ছে।', 'email');
        }
    }

    public function options()
    {
        return response()->json(['success' => true, 'data' => ['branches' => SisterConcern::where('status', 1)->orderBy('name')->get(['id', 'name', 'code'])]]);
    }

    private function sendOtp(string $mobile, string $otp): void
    {
        if (! app()->environment(['local', 'testing'])) {
            abort_unless(app(SmsService::class)->sendSms($mobile, "আপনার তরি নিবন্ধন ওটিপি: {$otp}। এটি ৩ মিনিট কার্যকর থাকবে।"), 503, 'Unable to deliver OTP. Please try again later.');
        }
    }

    public function initiate(Request $r)
    {
        $r->merge(['mobile_no' => $this->mobile((string) $r->input('mobile_no'))]);
        $d = $r->validate([
            'name' => 'required|string|max:255', 'mobile_no' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => 'nullable|email|max:255', 'branch_id' => 'required|integer|exists:sister_concerns,id',
            'password' => 'required|string|min:8|max:128|confirmed', 'terms_accepted' => 'required|accepted',
            'referral_code' => 'nullable|string|max:64', 'device_id' => 'required|string|max:128',
            'device_name' => 'nullable|string|max:255', 'platform' => 'nullable|string|max:32',
        ]);
        $this->ensureNew($r, $d['mobile_no'], $d['email'] ?? null);
        $token = Str::random(64);
        $otp = app()->environment('local') ? '123456' : (string) random_int(100000, 999999);
        DB::transaction(function () use ($r, $d, $token, $otp) {
            $branch = SisterConcern::whereKey($d['branch_id'])->where('status', 1)->lockForUpdate()->first();
            if (! $branch) $this->fail($r, 'Select an active branch.', 'সক্রিয় শাখা নির্বাচন করুন।', 'branch_id');
            $old = DB::table('membership_registration_requests')->where('mobile_no', $d['mobile_no'])->lockForUpdate()->first();
            if ($old && Carbon::parse($old->otp_sent_at)->addSeconds(60)->isFuture()) abort(429, $this->message($r, 'Wait 60 seconds before requesting another OTP.', 'আরেকটি ওটিপির জন্য ৬০ সেকেন্ড অপেক্ষা করুন।'));
            $this->ensureNew($r, $d['mobile_no'], $d['email'] ?? null);
            DB::table('membership_registration_requests')->updateOrInsert(['mobile_no' => $d['mobile_no']], [
                'session_hash' => hash('sha256', $token), 'device_id' => $d['device_id'],
                'device_name' => $d['device_name'] ?? null, 'platform' => $d['platform'] ?? null,
                'branch_id' => $branch->id, 'name' => trim($d['name']), 'email' => $d['email'] ?? null,
                'website_password' => Hash::make($d['password']), 'referral_code' => $d['referral_code'] ?? null,
                'terms_accepted_at' => now(), 'otp_hash' => Hash::make($otp), 'otp_attempts' => 0,
                'otp_expires_at' => now()->addMinutes(3), 'otp_sent_at' => now(),
                'verification_hash' => null, 'verified_until' => null, 'customer_id' => null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->sendOtp($d['mobile_no'], $otp);
        }, 3);
        return response()->json(['success' => true, 'message' => $this->message($r, 'OTP sent. Verify your mobile number.', 'ওটিপি পাঠানো হয়েছে। মোবাইল নম্বর যাচাই করুন।'),
            'data' => ['registration_token' => $token, 'mobile_no' => $d['mobile_no'], 'otp_expires_in_seconds' => 180, 'resend_wait_seconds' => 60, 'debug_otp' => app()->environment('local') ? $otp : null]]);
    }

    private function session(Request $r): object
    {
        $row = DB::table('membership_registration_requests')->where('session_hash', hash('sha256', $r->registration_token))->lockForUpdate()->first();
        abort_unless($row && hash_equals($row->device_id, $r->device_id), 401, $this->message($r, 'Registration session expired. Start again.', 'নিবন্ধন সেশনের মেয়াদ শেষ। আবার শুরু করুন।'));
        return $row;
    }

    private function validateSession(Request $r, array $extra = []): void
    {
        $r->validate($extra + ['registration_token' => 'required|string|size:64', 'device_id' => 'required|string|max:128']);
    }

    public function resend(Request $r)
    {
        $this->validateSession($r);
        return DB::transaction(function () use ($r) {
            $row = $this->session($r);
            abort_if($row->customer_id, 409, 'Registration already completed. Please sign in.');
            abort_if(Carbon::parse($row->otp_sent_at)->addSeconds(60)->isFuture(), 429, $this->message($r, 'Wait before requesting another OTP.', 'নতুন ওটিপির জন্য অপেক্ষা করুন।'));
            $otp = app()->environment('local') ? '123456' : (string) random_int(100000, 999999);
            DB::table('membership_registration_requests')->where('id', $row->id)->update(['otp_hash' => Hash::make($otp), 'otp_attempts' => 0, 'otp_expires_at' => now()->addMinutes(3), 'otp_sent_at' => now(), 'verification_hash' => null, 'verified_until' => null, 'updated_at' => now()]);
            $this->sendOtp($row->mobile_no, $otp);
            return response()->json(['success' => true, 'data' => ['resend_wait_seconds' => 60, 'debug_otp' => app()->environment('local') ? $otp : null]]);
        });
    }

    public function verify(Request $r)
    {
        $this->validateSession($r, ['otp' => 'required|digits:6']);
        return DB::transaction(function () use ($r) {
            $row = $this->session($r);
            abort_if($row->customer_id, 409, 'Registration already completed. Please sign in.');
            if (! $row->otp_hash || ! $row->otp_expires_at || Carbon::parse($row->otp_expires_at)->isPast()) {
                return response()->json(['success' => false, 'message' => $this->message($r, 'OTP expired. Request a new code.', 'ওটিপির মেয়াদ শেষ। নতুন ওটিপি নিন।')], 422);
            }
            if ($row->otp_attempts >= 5) return response()->json(['success' => false, 'message' => $this->message($r, 'Too many attempts. Request a new OTP.', 'অনেকবার ভুল হয়েছে। নতুন ওটিপি নিন।')], 429);
            if (! Hash::check($r->otp, $row->otp_hash)) {
                DB::table('membership_registration_requests')->where('id', $row->id)->increment('otp_attempts');
                return response()->json(['success' => false, 'message' => $this->message($r, 'Incorrect OTP.', 'ওটিপি সঠিক নয়।')], 422);
            }
            $token = Str::random(64);
            DB::table('membership_registration_requests')->where('id', $row->id)->update(['otp_hash' => null, 'verification_hash' => hash('sha256', $token), 'verified_until' => now()->addMinutes(15), 'updated_at' => now()]);
            return response()->json(['success' => true, 'data' => ['verification_token' => $token, 'has_pin' => false]]);
        });
    }

    public function complete(Request $r)
    {
        $this->validateSession($r, ['verification_token' => 'required|string|size:64', 'pin' => 'required|digits:6|confirmed']);
        return DB::transaction(function () use ($r) {
            $row = $this->session($r);
            abort_if($row->customer_id, 409, $this->message($r, 'Registration already completed. Please sign in.', 'নিবন্ধন ইতিমধ্যে সম্পন্ন হয়েছে। লগইন করুন।'));
            abort_unless($row->verification_hash && hash_equals($row->verification_hash, hash('sha256', $r->verification_token)) && $row->verified_until && Carbon::parse($row->verified_until)->isFuture(), 401, $this->message($r, 'Verify your mobile number again.', 'মোবাইল নম্বর আবার যাচাই করুন।'));
            $branch = SisterConcern::whereKey($row->branch_id)->where('status', 1)->lockForUpdate()->first();
            if (! $branch) $this->fail($r, 'The selected branch is inactive.', 'নির্বাচিত শাখা নিষ্ক্রিয়।');
            $this->ensureNew($r, $row->mobile_no, $row->email);
            $customer = Supplier::create(['name' => $row->name, 'mobile_no' => $row->mobile_no, 'email' => $row->email,
                'type' => 'Customer', 'origin_sister_concern_id' => $branch->id,
                'member_no' => generatePartyMemberNo($branch->id, 'Customer'), 'status' => 1,
                'password' => $row->website_password, 'pin' => Hash::make($r->pin)]);
            $proof = Str::random(64);
            CustomerDevice::create(['customer_id' => $customer->id, 'device_id' => $row->device_id,
                'device_name' => $row->device_name, 'platform' => $row->platform,
                'trusted_token_hash' => hash('sha256', $proof), 'last_active_at' => now()]);
            $token = $customer->createToken($row->device_name ?: 'TORI Registration')->plainTextToken;
            DB::table('membership_registration_requests')->where('id', $row->id)->update([
                'customer_id' => $customer->id, 'website_password' => '', 'verification_hash' => null,
                'verified_until' => null, 'otp_hash' => null, 'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => $this->message($r, 'Registration successful. Complete your profile and KYC.', 'নিবন্ধন সফল। আপনার প্রোফাইল ও কেওয়াইসি সম্পন্ন করুন।'),
                'data' => ['token' => $token, 'device_token' => $proof, 'customer' => ['id' => $customer->id,
                    'name' => $customer->name, 'member_no' => $customer->member_no, 'mobile_no' => $customer->mobile_no,
                    'email' => $customer->email, 'image' => null, 'is_biometric_enabled' => false]]], 201);
        }, 3);
    }
}
