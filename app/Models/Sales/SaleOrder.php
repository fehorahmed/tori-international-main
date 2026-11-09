<?php

namespace App\Models\Sales;

use App\Models\Account\Voucher;
use App\Models\Hrm\Employee;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Settings\Warehouse;
use App\Models\ReturnsManagement\SalesReturn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];
    
    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }
    public function saleOrderItems() {
        return $this->hasMany(SaleOrderItem::class, 'sale_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class,'customer_id','id');
    }
    public function sr()
    {
        return $this->belongsTo(Employee::class, 'sr_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'sale_order_id');
    }

    public function emiSaleDetail() {
        return $this->hasOne(EmiSaleDetail::class, 'sale_order_id');
    }

    public function hasActiveWarrantyItems()
    {
        if (!$this->relationLoaded('saleOrderItems')) {
            $this->load('saleOrderItems.product');
        }
        foreach ($this->saleOrderItems as $item) {
            if ($item->hasActiveWarranty()) {
                return true;
            }
        }
        return false;
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class, 'sale_order_id');
    }
}
