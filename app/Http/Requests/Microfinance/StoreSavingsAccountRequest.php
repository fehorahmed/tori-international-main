<?php

namespace App\Http\Requests\Microfinance;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We can add permission checks here if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:mf_members,id',
            'mf_savings_product_id' => 'required|exists:mf_savings_products,id',
            'opening_balance' => 'required|numeric|min:0',
            'nominee_id' => 'required|array|min:1',
            'nominee_id.*' => 'exists:mf_nominees,id',
            'share_percentage' => 'required|array',
            'opening_date' => 'required|date_format:d-m-Y',
        ];
    }

    /**
     * Custom error messages (Optional)
     */
    public function messages(): array
    {
        return [
            'nominee_id.required' => 'Please select at least one nominee.',
            'opening_date.date_format' => 'Opening date must be in dd-mm-yyyy format.',
        ];
    }
}
