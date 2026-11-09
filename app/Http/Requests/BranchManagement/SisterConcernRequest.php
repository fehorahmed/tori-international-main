<?php

namespace App\Http\Requests\BranchManagement;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SisterConcernRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $sisterConcernId = $this->route('sister_concern') ? $this->route('sister_concern')->id : null;

        $rules = [
            'name' => [
                'required', 'max:255',
                Rule::unique('sister_concerns')->ignore($sisterConcernId)
            ],
            'business_date' => ['required', 'date'],
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
            'auth_signature' => ['nullable', 'file', 'image', 'max:2048'],
            'official_seal' => ['nullable', 'file', 'image', 'max:2048'],
            'trade_license_no' => ['nullable', 'max:255'],
            'tin_no' => ['nullable', 'max:255'],
            'bin_no' => ['nullable', 'max:255'],
            'email' => ['nullable', 'max:255'],
            'contact' => ['nullable', 'max:255'],
            'address' => ['nullable', 'max:255'],
            'district' => ['nullable', 'max:255'],
            'upazila' => ['nullable', 'max:255'],
            'post_code' => ['nullable', 'max:255'],
            'office_rent' => ['nullable', 'numeric'],
            'electricity_bill' => ['nullable', 'numeric'],
            'establishment_date' => ['nullable', 'date'],
            'certificate_expiry_date' => ['nullable', 'date'],
            'status' => ['required'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['code'] = [
                'required', 'max:255',
                Rule::unique('sister_concerns')->ignore($sisterConcernId)
            ];
        }

        return $rules;
    }
}
