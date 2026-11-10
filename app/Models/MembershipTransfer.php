<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipTransfer extends Model
{
    protected $guarded = [];

    public function sender()
    {
        return $this->belongsTo(Supplier::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Supplier::class, 'recipient_id');
    }
}
