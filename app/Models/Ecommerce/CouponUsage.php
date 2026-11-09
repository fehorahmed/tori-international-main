<?php

namespace App\Models\Ecommerce;

use App\Models\Sales\SaleOrder;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'order_id',
        'discount',
    ];

    // Relationships
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(SaleOrder::class, 'order_id');
    }
}
