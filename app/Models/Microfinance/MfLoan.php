<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\Auditable;

class MfLoan extends Model
{
    use SoftDeletes, Auditable;

    protected $guarded = [];

    protected $casts = [
        'application_date' => 'date',
        'disbursement_date' => 'date',
        'first_installment_date' => 'date',
    ];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function somiti()
    {
        return $this->belongsTo(MfSomiti::class, 'somiti_id');
    }

    public function member()
    {
        return $this->belongsTo(MfMember::class, 'member_id');
    }

    public function product()
    {
        return $this->belongsTo(MfLoanProduct::class, 'mf_loan_product_id');
    }

    public function installments()
    {
        return $this->hasMany(MfInstallment::class, 'mf_loan_id');
    }
}
