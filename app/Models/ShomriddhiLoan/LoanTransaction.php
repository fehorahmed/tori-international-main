<?php

namespace App\Models\ShomriddhiLoan;

use App\Models\User;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id');
    }

    public function installment()
    {
        return $this->belongsTo(LoanInstallment::class, 'loan_installment_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'origin_sister_concern_id');
    }
}
