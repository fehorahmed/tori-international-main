<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipAgent extends Model
{
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public static function data(int $customer): ?array
    {
        $a = static::where('customer_id', $customer)->first();

        return $a ? ['id' => $a->id, 'code' => $a->agent_code, 'status' => $a->status] : null;
    }
}
