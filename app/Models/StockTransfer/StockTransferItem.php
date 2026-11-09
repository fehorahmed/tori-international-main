<?php

namespace App\Models\StockTransfer;

use App\Models\Inventory\Inventory;
use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    
    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }
}
