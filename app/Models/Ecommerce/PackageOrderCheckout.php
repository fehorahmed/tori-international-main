<?php

namespace App\Models\Ecommerce;

use App\Models\Sales\SaleOrder;
use Illuminate\Database\Eloquent\Model;

class PackageOrderCheckout extends Model
{
    protected $guarded = [];

    protected $casts = ['billing' => 'array', 'lines' => 'array'];

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }
}
