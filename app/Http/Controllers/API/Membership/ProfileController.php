<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'member_no' => $customer->member_no,
                'mobile_no' => $customer->mobile_no,
                'email' => $customer->email,
                'image' => $customer->image ? asset($customer->image) : null,
                'address' => $customer->address,
                'status' => $customer->status,
                'is_biometric_enabled' => (bool) $customer->is_biometric_enabled,
            ]
        ]);
    }
}

