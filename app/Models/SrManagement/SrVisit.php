<?php

namespace App\Models\SrManagement;


use App\Models\Hrm\Employee;
use App\Models\User;
use App\Models\Crm\CrmClient;
use Illuminate\Database\Eloquent\Model;

class SrVisit extends Model
{
    protected $guarded = [];

    public function sr()
    {
        return $this->belongsTo(Employee::class, 'sr_id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
