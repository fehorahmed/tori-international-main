<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;

class AttendanceReminderLog extends Model
{
    protected $guarded = ['id'];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
