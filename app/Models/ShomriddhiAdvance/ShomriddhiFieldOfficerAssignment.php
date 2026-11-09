<?php

namespace App\Models\ShomriddhiAdvance;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ShomriddhiFieldOfficerAssignment extends Model
{
    use Auditable;

    protected $table = 'shomriddhi_field_officer_assignments';

    protected $guarded = [];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'status' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(ShomriddhiGroup::class, 'shomriddhi_group_id');
    }

    public function fieldOfficer()
    {
        return $this->belongsTo(Employee::class, 'field_officer_id')->fieldOfficers();
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'sister_concern_id');
    }
}
