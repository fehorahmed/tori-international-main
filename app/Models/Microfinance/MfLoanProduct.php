<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Model;

class MfLoanProduct extends Model
{
    protected $guarded = [];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function loans()
    {
        return $this->hasMany(MfLoan::class);
    }
}
