<?php

namespace App\Models;

use App\Models\BranchManagement\SisterConcern;
use App\Services\Membership\CashoutService;
use Illuminate\Database\Eloquent\Model;

class MembershipCashout extends Model
{
    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime', 'accepted_at' => 'datetime', 'completed_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function branch()
    {
        return $this->belongsTo(SisterConcern::class, 'branch_id');
    }

    public function effectiveStatus(): string
    {
        return $this->status === 'Pending' && $this->expires_at->isPast() ? 'Expired' : $this->status;
    }

    public static function held(int $accountId, ?int $excludeId = null): int
    {
        return CashoutService::minor(static::where('account_id', $accountId)
            ->when($excludeId, fn ($q) => $q->whereKeyNot($excludeId))
            ->where(fn ($q) => $q->where('status', 'Accepted')->orWhere(fn ($q) => $q->where('status', 'Pending')->where('expires_at', '>', now())))
            ->sum('total'));
    }
}
