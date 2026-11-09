<?php

namespace App\Models\IncentiveManagement;

use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncentiveBalance extends Model
{
    protected $table = 'incentive_balances';
    protected $guarded = [];

    protected $casts = [
        'total_earned' => 'decimal:2',
        'total_paid'   => 'decimal:2',
        'balance'      => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function sisterConcern(): BelongsTo
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(IncentiveLedger::class);
    }
}
