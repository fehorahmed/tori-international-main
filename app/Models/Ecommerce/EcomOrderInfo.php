<?php

namespace App\Models\Ecommerce;

use App\Models\Sales\SaleOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcomOrderInfo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->hasMany(SaleOrder::class, 'order_id', 'id');
    }
    public function user(){
        return $this->belongsTo(User::class,'id','employee_id');
    }
}

