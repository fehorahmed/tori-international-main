<?php

namespace App\Models\Ecommerce;

use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CartItem extends Model
{
    // protected $guarded = [];
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'old_price',
        'price',
        'discount',
    ];

    protected $appends = ['discount_percent'];
    public function getDiscountPercentAttribute(): float
    {
        return (float) $this->old_price > 0 ? round((float) $this->discount / (float) $this->old_price * 100, 2) : 0;
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Link to the Product
    public function product()
    {
        return $this->belongsTo(Product::class)->select('id','name','image','catalog_type','package_warehouse_id','status');
    }
}

