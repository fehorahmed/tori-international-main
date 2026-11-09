<?php

namespace App\Models\Sales;

use App\Models\Inventory\Inventory;
use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrderItem extends Model
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
    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function getWarrantyExpiryDateAttribute()
    {
        if ($this->product && $this->product->has_warranty && $this->product->warranty_duration) {
            $saleDate = \Carbon\Carbon::parse($this->date);
            if ($this->product->warranty_type == 'Year') {
                return $saleDate->addYears((int)$this->product->warranty_duration);
            } else {
                return $saleDate->addMonths((int)$this->product->warranty_duration);
            }
        }
        return null;
    }

    public function hasActiveWarranty()
    {
        $expiryDate = $this->warranty_expiry_date;
        if ($expiryDate) {
            return \Carbon\Carbon::now()->lessThanOrEqualTo($expiryDate);
        }
        return false;
    }
}
