<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipAgentTransaction extends Model
{
    protected $guarded = [];

    protected $casts = ['settings_snapshot' => 'array'];

    public function transfer()
    {
        return $this->belongsTo(MembershipTransfer::class);
    }

    public function agent()
    {
        return $this->belongsTo(MembershipAgent::class);
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }
}
