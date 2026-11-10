<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\CustomerKyc;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $customer = KycController::customer($request);
        $kyc = CustomerKyc::where('customer_id', $customer->id)->latest('id')->first();
        $kycData = KycController::data($kyc);
        if ($kyc?->status === 'Verified' && $kyc->submitted_name !== $customer->name) {
            $kycData['status'] = 'Needs resubmission';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'member_no' => $customer->member_no,
                'mobile_no' => $customer->mobile_no,
                'email' => $customer->email,
                'image' => $kyc ? url("/api/membership/v1/kyc/{$kyc->id}/documents/selfie")
                    : ($customer->image ? asset($customer->image) : null),
                'address' => $customer->address,
                'status' => $customer->status,
                'is_biometric_enabled' => (bool) $customer->is_biometric_enabled,
                'is_verified' => $kycData['status'] === 'Verified',
                'kyc' => $kycData,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $customer = KycController::customer($request);
        $data = KycController::validateInput($request, [
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($customer->id)],
            'address' => 'nullable|string|max:2000',
        ]);

        return DB::transaction(function () use ($customer, $data, $request) {
            Supplier::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $customer->refresh();
            if ($data['name'] !== $customer->name) {
                $kyc = CustomerKyc::where('customer_id', $customer->id)->latest('id')->first();
                if ($kyc && in_array($kyc->status, ['Pending', 'Verified'], true)) {
                    return response()->json(['success' => false, 'message' => KycController::message($request,
                        'Please contact your branch to change your name after submitting KYC.',
                        'কেওয়াইসি জমা দেওয়ার পর নাম পরিবর্তনের জন্য আপনার শাখায় যোগাযোগ করুন।')], 422);
                }
            }
            $customer->fill($data)->save();

            return $this->getProfile($request);
        });
    }
}
