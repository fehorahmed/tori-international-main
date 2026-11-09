<?php

namespace App\Models\ShomriddhiDps;

use Illuminate\Database\Eloquent\Model;

class DpsTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function dpsAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DpsAccount::class, 'dps_account_id');
    }

    public function voucher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Account\Voucher::class, 'voucher_id');
    }

    public function paymentHead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Account\AccountHead::class, 'payment_account_head_id');
    }
}
