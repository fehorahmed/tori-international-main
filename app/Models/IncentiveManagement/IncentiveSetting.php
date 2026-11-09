<?php

namespace App\Models\IncentiveManagement;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Settings\Product;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncentiveSetting extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'incentive_settings';

    protected $guarded = [];

    protected $casts = [
        'sr_percent' => 'decimal:2',
        'bm_percent' => 'decimal:2',
        'am_percent' => 'decimal:2',
        'sr_active'  => 'boolean',
        'bm_active'  => 'boolean',
        'am_active'  => 'boolean',
    ];

    // ─── Scopes ───────────────────────────────────────────────────

    /** শুধুমাত্র SR এর জন্য active settings */
    public function scopeActiveSr($query)
    {
        return $query->where('sr_active', true);
    }

    /** শুধুমাত্র BM এর জন্য active settings */
    public function scopeActiveBm($query)
    {
        return $query->where('bm_active', true);
    }

    /** শুধুমাত্র AM এর জন্য active settings */
    public function scopeActiveAm($query)
    {
        return $query->where('am_active', true);
    }

    /** যেকোনো একটি role active থাকলেই setting টি active ধরা হবে */
    public function scopeAnyActive($query)
    {
        return $query->where(function ($q) {
            $q->where('sr_active', true)
              ->orWhere('bm_active', true)
              ->orWhere('am_active', true);
        });
    }

    public function scopeForConcern($query, int $concernId)
    {
        return $query->where('sister_concern_id', $concernId);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /** কোনো role active আছে কিনা চেক করো */
    public function isAnyRoleActive(): bool
    {
        return $this->sr_active || $this->bm_active || $this->am_active;
    }

    // ─── Relationships ────────────────────────────────────────────

    public function sisterConcern(): BelongsTo
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
