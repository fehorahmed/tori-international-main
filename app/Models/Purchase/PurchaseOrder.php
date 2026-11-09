<?php

namespace App\Models\Purchase;

use App\Models\Approval;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Settings\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
