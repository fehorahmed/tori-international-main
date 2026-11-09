<?php

namespace App\Models\Payroll;

use App\Models\Hrm\Employee;
use App\Models\Account\Voucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class SalaryProcessItem extends Model
{
    use HasFactory, Auditable;
    protected $guarded = [];

    public function salaryProcess()
    {
        return $this->belongsTo(SalaryProcess::class);
    }
    public function jVoucher()
    {
        return $this->belongsTo(Voucher::class,'jv_voucher_id');
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class)
            ->with('designation','department');
    }
}
