<?php

namespace App\Http\Requests\Hrm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermissionTo('employee_create');
    }

    public function rules(): array
    {
        return [
            'id_no'              => ['required', 'max:255', Rule::unique('employees')],
            'name'               => 'required|max:255',
            'department'         => 'required',
            'designation'        => 'required',
            'employee_status'    => 'required',
            'mobile_no'          => [
                'required', 'string', 'max:15',
                Rule::unique('employees', 'mobile_no'),
                Rule::unique('users', 'mobile_no'),
            ],
            'email'              => [
                'nullable', 'string', 'email:rfc,dns', 'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('employees', 'email'),
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
            'id_no.required'         => 'Employee ID is required.',
            'id_no.unique'           => 'This Employee ID is already taken.',
            'name.required'          => 'Employee name is required.',
            'department.required'    => 'Please select a department.',
            'designation.required'   => 'Please select a designation.',
            'employee_status.required' => 'Please select an employee status.',
            'mobile_no.required'     => 'Mobile number is required.',
            'mobile_no.unique'       => 'This mobile number is already registered.',
            'shift.required'         => 'Please select a shift.',
            'gross_salary.required'  => 'Gross salary is required.',
        ];
    }
}
