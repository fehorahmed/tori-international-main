<?php

namespace App\Models\StockConversion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\Product;
use App\Models\Inventory\Inventory;
use App\Models\StockConversion\StockConversion;

class StockConversionItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function stockConversion()
    {
        return $this->belongsTo(StockConversion::class);
    }

    public function fromProduct()
    {
        return $this->belongsTo(Product::class, 'from_product_id');
    }

    public function toProduct()
    {
        return $this->belongsTo(Product::class, 'to_product_id');
    }

    public function fromInventory()
    {
        return $this->belongsTo(Inventory::class, 'from_inventory_id');
    }
}
