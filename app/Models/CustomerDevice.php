<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDevice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ['trusted_token_hash'];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }
}
