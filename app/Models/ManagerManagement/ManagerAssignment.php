<?php

namespace App\Models\ManagerManagement;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Model;

class ManagerAssignment extends Model
{
    protected $fillable = [
        'manager_id', 'sister_concern_id', 'manager_type', 
        'assigned_date', 'unassigned_date', 'status'
    ];

    public function manager() {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function sisterConcern() {
        return $this->belongsTo(SisterConcern::class, 'sister_concern_id');
    }
}
