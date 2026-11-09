<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Model;

class MfInstallment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function loan()
    {
        return $this->belongsTo(MfLoan::class, 'mf_loan_id');
    }

    public function somiti()
    {
        return $this->belongsTo(MfSomiti::class, 'somiti_id');
    }
}
