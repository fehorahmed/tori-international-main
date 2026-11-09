<?php

namespace App\Models\EmployeeProfile;

use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Model;

class EmployeeExperience extends Model
{
    protected $table = 'employee_experiences';
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
