<?php

namespace App\Models\CompanyLoan;

use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lender_concern_id', 'borrower_concern_id', 'amount', 'interest_rate', 'duration', 'loan_type', 'calculation_method', 'start_date', 'status',
        'lender_account_head_id', 'borrower_account_head_id', 'notes',
        'approved_by', 'approved_at', 'created_by'
    ];

    public function lender()
    {
        return $this->belongsTo(SisterConcern::class, 'lender_concern_id');
    }

    public function borrower()
    {
        return $this->belongsTo(SisterConcern::class, 'borrower_concern_id');
    }

    public function lenderAccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'lender_account_head_id');
    }

    public function borrowerAccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'borrower_account_head_id');
    }

    public function installments()
    {
        return $this->hasMany(CompanyLoanInstallment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
