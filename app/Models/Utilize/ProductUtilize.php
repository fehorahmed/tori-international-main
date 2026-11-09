<?php

namespace App\Models\Utilize;

use App\Models\User;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUtilize extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function productUtilizeItems()
    {
        return $this->hasMany(ProductUtilizeItem::class);
    }
    public function voucher()
    {
        return $this->hasOne(Voucher::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
