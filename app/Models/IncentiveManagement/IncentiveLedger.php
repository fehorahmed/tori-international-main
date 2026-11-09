<?php

namespace App\Models\IncentiveManagement;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentiveLedger extends Model
{
    protected $table = 'incentive_ledger';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ─── Role Type Constants ──────────────────────────────────────

    const ROLE_SR      = 'sr';
    const ROLE_BM      = 'branch_manager';
    const ROLE_AM      = 'area_manager';

    // ─── Type Constants ───────────────────────────────────────────

    const TYPE_EARNED   = 'earned';
    const TYPE_RETURNED = 'returned';
    const TYPE_PAID     = 'paid';

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForConcern($query, int $concernId)
    {
        return $query->where('sister_concern_id', $concernId);
    }

    public function scopePending($query)
    {
        return $query->where('type', self::TYPE_EARNED)
                     ->where('payment_status', 'unpaid');
    }

    // ─── Relationships ────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function sisterConcern(): BelongsTo
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Get current pending balance for an employee in a concern.
     * Uses SUM — fast with composite index.
     */
    public static function getBalance(int $employeeId, int $concernId): float
    {
        return (float) static::where('employee_id', $employeeId)
            ->where('sister_concern_id', $concernId)
            ->where('type', self::TYPE_EARNED)
            ->where('payment_status', 'unpaid')
            ->sum('amount');
    }}
