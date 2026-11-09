<?php

namespace App\Http\Requests\Hrm;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermissionTo('employee_edit');
    }

    public function rules(): array
    {
        $employee = $this->route('employee'); // Route Model Binding থেকে Employee পাওয়া
        $userId   = User::where('employee_id', $employee->id)->value('id');

        return [
            'name'            => 'required|max:255',
            'employee_status' => 'required',
            'mobile_no'       => [
                'required', 'string', 'max:15',
                Rule::unique('employees', 'mobile_no')->ignore($employee->id),
                Rule::unique('users', 'mobile_no')->ignore($userId),
            ],
            'email'           => [
                'nullable', 'string', 'email:rfc,dns', 'max:255',
                Rule::unique('employees', 'email')->ignore($employee->id),
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'shift'              => 'required',
            'salary_structure_id'=> 'required',
            'gross_salary'       => 'required|numeric|min:0',
            'bank_name'          => 'nullable|max:255',
            'bank_account_no'    => 'nullable|max:255',
            'photo'              => 'nullable|mimes:jpg,jpeg,png|max:10048',
            'signature_photo'    => 'nullable|mimes:jpg,jpeg,png|max:10048',
            'nid'                => 'nullable|mimes:jpg,jpeg,png|max:10048',
            'cv'                 => 'nullable|mimes:pdf|max:10048',
            // Address fields
            'current_division'   => 'nullable',
            'current_district'   => 'nullable',
            'current_upazila'    => 'nullable',
            'current_union'      => 'nullable',
            'current_village_id' => 'nullable',
            'permanent_division'   => 'nullable',
            'permanent_district'   => 'nullable',
            'permanent_upazila'    => 'nullable',
            'permanent_union'      => 'nullable',
            'permanent_village_id' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Employee name is required.',
            'employee_status.required' => 'Please select an employee status.',
            'mobile_no.required'       => 'Mobile number is required.',
            'mobile_no.unique'         => 'This mobile number is already registered.',
            'shift.required'           => 'Please select a shift.',
            'salary_structure_id.required' => 'Please select a salary structure.',
            'gross_salary.required'    => 'Gross salary is required.',
        ];
    }
}
