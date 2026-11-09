<?php

namespace App\Models\SrManagement;

use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Model;

class SrSocialEngagement extends Model
{
    protected $guarded = [];

    public function sr()
    {
        return $this->belongsTo(\App\Models\Hrm\Employee::class, 'sr_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
