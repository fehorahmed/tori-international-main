<?php

namespace App\Models\ReturnsManagement;

use App\Models\Inventory\Inventory;
use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturnItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
