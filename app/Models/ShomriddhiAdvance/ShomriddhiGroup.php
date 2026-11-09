<?php

namespace App\Models\ShomriddhiAdvance;

use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use App\Models\Village;
use App\Models\Supplier;

class ShomriddhiGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_bn',
        'code',
        'division_id',
        'district_id',
        'upazila_id',
        'union_id',
        'pourashava_id',
        'village_id',
        'field_officer_id',
        'opening_date',
        'meeting_time',
        'collection_day',
        'status',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function pourashava()
    {
        return $this->belongsTo(Pourashava::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function fieldOfficer()
    {
        return $this->belongsTo(Employee::class, 'field_officer_id');
    }

    public function fieldOfficerAssignments()
    {
        return $this->hasMany(ShomriddhiFieldOfficerAssignment::class, 'shomriddhi_group_id')->orderBy('from_date', 'desc');
    }

    public function members()
    {
        return $this->hasMany(Supplier::class, 'shomriddhi_group_id');
    }
}
