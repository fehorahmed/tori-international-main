<?php

namespace App\Models\BranchManagement;

use App\Models\Hrm\Employee;
use App\Models\ManagerManagement\ManagerAssignment;
use App\Support\ApplicationDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisterConcern extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $branch) {
            unset($branch->attributes['business_date']);
        });
    }

    // Compatibility for existing callers: a business date is no longer stored per branch.
    public function getBusinessDateAttribute(): string
    {
        return ApplicationDate::today();
    }

    public function setBusinessDateAttribute($value): void
    {
        // Ignore legacy writes without altering the previously stored value.
    }

    /**
     * Get the display name for dropdowns (Branch Code - Branch Name)
     */
    public function getDisplayNameAttribute()
    {
        return $this->code ? $this->code.' - '.$this->name : $this->name;
    }

    /**
     * Current Area Manager (denormalized FK — fast lookup)
     */
    public function areaManager()
    {
        return $this->belongsTo(Employee::class, 'area_manager_id');
    }

    /**
     * Current Branch Manager (denormalized FK — fast lookup)
     */
    public function branchManager()
    {
        return $this->belongsTo(Employee::class, 'branch_manager_id');
    }

    /**
     * Full assignment history for this branch
     */
    public function managerAssignments()
    {
        return $this->hasMany(ManagerAssignment::class);
    }

    /**
     * Currently active Area Manager assignment (from history table)
     */
    public function activeAreaManagerAssignment()
    {
        return $this->hasOne(ManagerAssignment::class)
            ->where('manager_type', 'Area_Manager')
            ->where('status', 1)
            ->latestOfMany('assigned_date');
    }

    /**
     * Currently active Branch Manager assignment (from history table)
     */
    public function activeBranchManagerAssignment()
    {
        return $this->hasOne(ManagerAssignment::class)
            ->where('manager_type', 'Branch_Manager')
            ->where('status', 1)
            ->latestOfMany('assigned_date');
    }
}
