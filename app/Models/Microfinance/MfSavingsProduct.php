<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Model;

class MfSavingsProduct extends Model
{
    protected $guarded = [];

    const TYPE_GENERAL = 1;
    const TYPE_DPS = 2;
    const TYPE_FDR = 3;

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function accounts()
    {
        return $this->hasMany(MfSavingsAccount::class);
    }
}
