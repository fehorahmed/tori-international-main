<?php

namespace App\Models\Sales;

use App\Models\Account\Voucher;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $guarded = [];

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
