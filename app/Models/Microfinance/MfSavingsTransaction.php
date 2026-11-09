<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class MfSavingsTransaction extends Model
{
    use Auditable;
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    const TYPE_DEPOSIT  = 1;
    const TYPE_WITHDRAW = 2;
    const TYPE_INTEREST = 3;
    const TYPE_PENALTY  = 4;
    const TYPE_OPENING  = 5; // Migration Opening Balance

    public function savingsAccount()
    {
        return $this->belongsTo(MfSavingsAccount::class, 'mf_savings_account_id');
    }

    public function account()
    {
        return $this->savingsAccount();
    }

    public function somiti()
    {
        return $this->belongsTo(MfSomiti::class, 'somiti_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }
}
