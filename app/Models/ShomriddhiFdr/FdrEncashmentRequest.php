<?php

namespace App\Models\ShomriddhiFdr;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class FdrEncashmentRequest extends Model
{
    protected $guarded = [];

    protected $casts = ['preview' => 'array'];

    public function account()
    {
        return $this->belongsTo(FdrAccount::class, 'fdr_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }
}
