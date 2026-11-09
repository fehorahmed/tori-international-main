<?php

namespace App\Models\RoutePlan;

use Illuminate\Database\Eloquent\Model;

class RoutePlanCustomerType extends Model
{
    protected $guarded = [];

    public function customerType()
    {
        return $this->belongsTo(\App\Models\Crm\CustomerType::class, 'customer_type_id');
    }
}
