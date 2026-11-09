<?php

namespace App\Models\StockConversion;

use App\Models\Account\Voucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Settings\Warehouse;
use App\Models\User;
use App\Models\StockConversion\StockConversionItem;

class StockConversion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }

    public function items()
    {
        return $this->hasMany(StockConversionItem::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'stock_conversion_id');
    }
}
