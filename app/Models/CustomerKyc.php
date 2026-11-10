<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerKyc extends Model
{
    protected $guarded = [];

    protected $hidden = ['nid_front_path', 'nid_back_path', 'selfie_path'];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }
}
