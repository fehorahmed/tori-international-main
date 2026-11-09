<?php

namespace App\Models\Crm;

use App\Enumeration\SupplierType;
use App\Models\Supplier;
use App\Models\Upazila;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $guarded = [];

    public function upazila()
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }

    public function shops()
    {
        return $this->hasMany(Supplier::class, 'market_id');
    }
}
