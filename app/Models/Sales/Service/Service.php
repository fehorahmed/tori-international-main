<?php

namespace App\Models\Sales\Service;

use App\Models\Sales\SaleOrder;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Account\Voucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function technician()
    {
        return $this->belongsTo(Supplier::class, 'technician_id');
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }

    public function serviceUtilizedItems()
    {
        return $this->hasMany(ServiceUtilizedItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
