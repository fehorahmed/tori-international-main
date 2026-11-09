<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Hrm\Employee;
use App\Models\User;
use App\Models\BranchManagement\SisterConcern;

class EmployeeAdvance extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }
}
