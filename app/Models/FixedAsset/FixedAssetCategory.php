<?php

namespace App\Models\FixedAsset;

use App\Models\Account\AccountHead;
use Illuminate\Database\Eloquent\Model;

class FixedAssetCategory extends Model
{
    protected $fillable = [
        'name',
        'depreciation_method',
        'depreciation_rate',
        'useful_life_years',
        'account_head_id',
        'accumulated_dep_head_id',
        'note',
    ];

    /**
     * The Account Head automatically created for this category
     * e.g. "Furniture & Fixture" ledger under Fixed Assets group
     */
    public function accountHead()
    {
        return $this->belongsTo(AccountHead::class, 'account_head_id');
    }

    /**
     * Shared Accumulated Depreciation account head
     */
    public function accumulatedDepHead()
    {
        return $this->belongsTo(AccountHead::class, 'accumulated_dep_head_id');
    }

    /**
     * All assets belonging to this category
     */
    public function fixedAssets()
    {
        return $this->hasMany(FixedAsset::class, 'category_id');
    }

    /**
     * Count of active assets
     */
    public function activeAssetsCount()
    {
        return $this->fixedAssets()->where('status', 'active')->count();
    }

    /**
     * Check if the category is locked (has active assets or accounting transactions)
     */
    public function isLocked(): bool
    {
        if ($this->activeAssetsCount() > 0) {
            return true;
        }

        return \App\Models\Account\Transaction::where('account_head_id', $this->account_head_id)->exists();
    }
}
