<?php

namespace App\Models\FixedAsset;

use App\Models\Account\Voucher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FixedAssetDepreciationLog extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'sister_concern_id',
        'period',
        'depreciation_amount',
        'book_value_before',
        'book_value_after',
        'voucher_id',
        'user_id',
    ];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
