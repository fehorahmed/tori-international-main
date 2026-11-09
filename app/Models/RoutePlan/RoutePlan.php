<?php

namespace App\Models\RoutePlan;

use App\Models\Hrm\Employee;
use App\Models\Crm\Market;
use Illuminate\Database\Eloquent\Model;

class RoutePlan extends Model
{
    protected $guarded = [];

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id');
    }

    public function sr()
    {
        return $this->belongsTo(Employee::class, 'sr_id');
    }
}
