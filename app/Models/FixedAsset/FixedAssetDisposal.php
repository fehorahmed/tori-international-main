<?php

namespace App\Models\FixedAsset;

use App\Models\Account\AccountHead;
use App\Models\Account\Voucher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FixedAssetDisposal extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'sister_concern_id',
        'disposal_date',
        'disposal_type',
        'sale_amount',
        'book_value_at_disposal',
        'gain_loss_amount',
        'payment_account_head_id',
        'voucher_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'disposal_date'          => 'date',
        'sale_amount'            => 'decimal:2',
        'book_value_at_disposal' => 'decimal:2',
        'gain_loss_amount'       => 'decimal:2',
    ];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function paymentAccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'payment_account_head_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Returns true if this disposal resulted in a gain */
    public function isGain(): bool
    {
        return $this->gain_loss_amount > 0;
    }

    /** Returns true if this disposal resulted in a loss */
    public function isLoss(): bool
    {
        return $this->gain_loss_amount < 0;
    }
}
