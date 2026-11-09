<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\Auditable;

class MfSomiti extends Model
{
    use SoftDeletes, Auditable;

    protected $guarded = [];

    protected $casts = [
        'opening_date' => 'date',
        'collection_start_date' => 'date',
    ];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function members()
    {
        return $this->hasMany(MfMember::class, 'somiti_id');
    }

    public function fieldOfficer()
    {
        return $this->belongsTo(Employee::class, 'field_officer_id');
    }

    public function fieldOfficerAssignments()
    {
        return $this->hasMany(MfFieldOfficerAssignment::class, 'somiti_id')->orderBy('from_date', 'desc');
    }
}
