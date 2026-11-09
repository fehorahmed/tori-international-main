<?php

namespace App\Models\ReturnsManagement;

use App\Models\Supplier;
use App\Models\Sales\SaleOrder;
use App\Models\Settings\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function returnWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'return_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function createUser()
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }
}
