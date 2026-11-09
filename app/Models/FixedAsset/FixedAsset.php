<?php

namespace App\Models\FixedAsset;

use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\Voucher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_code',
        'name',
        'category_id',
        'sister_concern_id',
        'date',
        'total',
        'salvage_value',
        'useful_life_years',
        'depreciation_method',
        'depreciation_rate',
        'accumulated_depreciation',
        'book_value',
        'account_head_id',
        'payment_account_head_id',
        'location',
        'serial_no',
        'vendor',
        'warranty_expiry',
        'assigned_to_user_id',
        'status',
        'purchase_voucher_id',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'date'    => 'date',
        'book_value'       => 'decimal:2',
        'total'    => 'decimal:2',
        'salvage_value'    => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value'       => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(FixedAssetCategory::class, 'category_id');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'sister_concern_id');
    }

    /** The asset's own ledger account (e.g. "Furniture & Fixture") */
    public function accountHead()
    {
        return $this->belongsTo(AccountHead::class, 'account_head_id');
    }

    /** Cash/Bank used to purchase */
    public function paymentAccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'payment_account_head_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function purchaseVoucher()
    {
        return $this->belongsTo(Voucher::class, 'purchase_voucher_id');
    }

    public function depreciationLogs()
    {
        return $this->hasMany(FixedAssetDepreciationLog::class, 'fixed_asset_id');
    }

    public function disposal()
    {
        return $this->hasOne(FixedAssetDisposal::class, 'fixed_asset_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** Auto-generate next asset code: FA-2026-001 */
    public static function generateAssetCode(): string
    {
        $year = date('Y');
        $prefix = 'FA-' . $year . '-';
        $last = self::withTrashed()
            ->where('asset_code', 'like', $prefix . '%')
            ->max('asset_code');

        if ($last) {
            $lastNo = (int) substr($last, strlen($prefix));
            return $prefix . str_pad($lastNo + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }

    /** Calculate monthly depreciation amount */
    public function calculateMonthlyDepreciation(): float
    {
        if ($this->depreciation_method === 'none' || $this->book_value <= 0) {
            return 0;
        }

        if ($this->depreciation_method === 'SLM') {
            $annualDep = ($this->total - $this->salvage_value) / max(1, $this->useful_life_years);
            return round($annualDep / 12, 2);
        }

        if ($this->depreciation_method === 'WDV') {
            $annualDep = $this->book_value * ($this->depreciation_rate / 100);
            return round($annualDep / 12, 2);
        }

        return 0;
    }

    /** Check if depreciation already run for a given period (YYYY-MM) */
    public function isDepreciatedForPeriod(string $period): bool
    {
        return $this->depreciationLogs()->where('period', $period)->exists();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'      => '<span class="badge badge-success">Active</span>',
            'sold'        => '<span class="badge badge-info">Sold</span>',
            'scrapped'    => '<span class="badge badge-danger">Scrapped</span>',
            'transferred' => '<span class="badge badge-warning">Transferred</span>',
            default       => '<span class="badge badge-secondary">Unknown</span>',
        };
    }
}
