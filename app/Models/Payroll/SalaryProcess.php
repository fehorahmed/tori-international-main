<?php

namespace App\Models\Payroll;

use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Models\Account\Voucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class SalaryProcess extends Model
{
    use HasFactory, Auditable;
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryProcessItems()
    {
        return $this->hasMany(SalaryProcessItem::class);
    }

}
