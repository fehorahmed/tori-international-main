<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\CustomerKyc;
use App\Models\Supplier;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class KycController extends Controller
{
    public static function customer(Request $request): Supplier
    {
        abort_unless($request->user() instanceof Supplier, 403);
        abort_unless($request->user()->status == 1, 403);
        abort_if($request->user()->type === 'Supplier', 403);

        return $request->user();
    }

    public static function data(?CustomerKyc $kyc): array
    {
        return [
            'status' => $kyc?->status ?? 'Not submitted',
            'nid_number' => $kyc?->nid_number ?? '',
            'submitted_at' => $kyc?->created_at?->toIso8601String(),
            'reviewer_message' => $kyc?->reviewer_message,
            'documents' => $kyc ? collect(['nid_front', 'nid_back', 'selfie'])
                ->mapWithKeys(fn ($kind) => [$kind => url("/api/membership/v1/kyc/{$kyc->id}/documents/{$kind}")])->all() : (object) [],
        ];
    }

    public function store(Request $request)
    {
        $customer = self::customer($request);
        $data = self::validateInput($request, [
            'nid_number' => ['required', 'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17})$/'],
            'nid_front' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'nid_back' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'selfie' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);
        $paths = [];
        try {
            $kyc = DB::transaction(function () use ($customer, $request, $data, &$paths) {
                Supplier::whereKey($customer->id)->lockForUpdate()->firstOrFail();
                $customer->refresh();
                if (CustomerKyc::where('customer_id', $customer->id)->latest('id')->first()?->status === 'Pending') {
                    throw ValidationException::withMessages(['kyc' => [self::message($request,
                        'Your KYC is awaiting review. Please wait for a decision.',
                        'আপনার কেওয়াইসি যাচাইয়ের অপেক্ষায় আছে। সিদ্ধান্তের জন্য অপেক্ষা করুন।')]]);
                }
                foreach (['nid_front', 'nid_back', 'selfie'] as $kind) {
                    $path = $request->file($kind)->store("membership/{$customer->id}", 'kyc');
                    if (! $path) {
                        throw new \RuntimeException('Unable to store KYC document.');
                    }
                    $paths[$kind.'_path'] = $path;
                }

                return CustomerKyc::create($paths + ['customer_id' => $customer->id, 'submitted_name' => $customer->name, 'nid_number' => $data['nid_number'], 'status' => 'Pending']);
            });
        } catch (\Throwable $exception) {
            Storage::disk('kyc')->delete(array_values($paths));
            throw $exception;
        }

        return response()->json(['success' => true, 'message' => self::message($request,
            'KYC submitted. Your documents are awaiting review.',
            'কেওয়াইসি জমা হয়েছে। আপনার ডকুমেন্টগুলো যাচাইয়ের অপেক্ষায় আছে।'), 'data' => self::data($kyc)], 201);
    }

    public function document(Request $request, CustomerKyc $kyc, string $kind)
    {
        $customer = self::customer($request);
        abort_unless($kyc->customer_id == $customer->id, 403);
        abort_unless(in_array($kind, ['nid_front', 'nid_back', 'selfie'], true), 404);

        return Storage::disk('kyc')->response($kyc->{$kind.'_path'}, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public static function message(Request $request, string $en, string $bn): string
    {
        return $request->getPreferredLanguage(['en', 'bn']) === 'bn' ? $bn : $en;
    }

    public static function validateInput(Request $request, array $rules): array
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => self::message($request, $validator->errors()->first(),
                    'তথ্য বা ছবিতে সমস্যা আছে। সঠিক তথ্য ও JPG, PNG অথবা WebP ছবি দিন (প্রতিটি সর্বোচ্চ ৫ এমবি)।'),
                'errors' => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }
}
