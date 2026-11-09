<?php

namespace App\Models\CompanyLoan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyLoanInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function loan()
    {
        return $this->belongsTo(CompanyLoan::class, 'company_loan_id');
    }
}
