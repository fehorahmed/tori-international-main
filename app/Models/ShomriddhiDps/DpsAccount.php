<?php

namespace App\Models\ShomriddhiDps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Supplier;

class DpsAccount extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'auto_renew'   => 'boolean',
        'opening_date' => 'date',
        'maturity_date' => 'date',
        'next_installment_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    public function scheme(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DpsScheme::class, 'scheme_id');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DpsTransaction::class, 'dps_account_id');
    }

    public function nominees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DpsNominee::class, 'dps_account_id');
    }

    /**
     * Remaining installments to be paid.
     */
    public function getRemainingInstallmentsAttribute(): int
    {
        return $this->total_installments - $this->paid_installments;
    }

    /**
     * Completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_installments === 0) return 0;
        return round(($this->paid_installments / $this->total_installments) * 100, 2);
    }
}
