<?php

namespace App\Models\ReturnsManagement;

use App\Models\Supplier;
use App\Models\Settings\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function returnWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'return_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }
}
