<?php

namespace App\Models\ShomriddhiLoan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id');
    }
}
