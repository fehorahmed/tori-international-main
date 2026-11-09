<?php

namespace App\Models\Utilize;

use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUtilizeItem extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function inventoryLog()
    {
        return $this->hasOne(InventoryLog::class, 'product_id', 'product_id')
            ->where('product_utilize_id', $this->product_utilize_id);
    }
}
