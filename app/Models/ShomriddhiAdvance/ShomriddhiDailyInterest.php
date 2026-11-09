<?php

namespace App\Models\ShomriddhiAdvance;

use Illuminate\Database\Eloquent\Model;

class ShomriddhiDailyInterest extends Model
{
    protected $fillable = [
        'origin_sister_concern_id',
        'customer_id',
        'date',
        'principal_balance',
        'interest_rate',
        'earned_interest',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'customer_id');
    }
}
