<?php

namespace App\Models\Inventory;

use App\Models\StockConversion\StockConversion;
use App\Models\StockTransfer\StockTransfer;
use App\Models\Utilize\ProductUtilize;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Product;
use App\Models\Supplier;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Sales\SaleOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ReturnsManagement\SalesReturn;
use App\Models\ReturnsManagement\PurchaseReturn;

class InventoryLog extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }
    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }
    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
    public function productUtilize()
    {
        return $this->belongsTo(ProductUtilize::class);
    }
    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }
    public function stockConversion()
    {
        return $this->belongsTo(StockConversion::class);
    }
}
