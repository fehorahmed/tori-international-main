<?php

namespace App\Models\Hrm;

use App\Enumeration\ReservedDesignation;
use App\Models\BranchManagement\SisterConcern;
use App\Models\CardManagement\Card;
use App\Models\District;
use App\Models\Division;
use App\Models\Payroll\SalaryStructure;
use App\Models\Pourashava;
use App\Models\RoutePlan\RoutePlan;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Payroll\EmployeeAdvance;
use App\Models\Account\Transaction;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;

class Employee extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function employeeAdvances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'employee_id');
    }

    public function educations()
    {
        return $this->hasMany(\App\Models\EmployeeProfile\EmployeeEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(\App\Models\EmployeeProfile\EmployeeExperience::class);
    }

    public function documents()
    {
        return $this->hasMany(\App\Models\EmployeeProfile\EmployeeDocument::class);
    }

    public function scopeWithAdvanceBalance($query)
    {
        $advanceHeadId = AccountHead::where('reserved_id', ReservedAccountHead::EMPLOYEE_ADVANCE)->value('id');

        return $query->whereHas('transactions', function($q) use ($advanceHeadId) {
                $q->where('account_head_id', $advanceHeadId);
            })
            ->withSum(['transactions as total_debit' => function($q) use ($advanceHeadId) {
                $q->where('account_head_id', $advanceHeadId)->where('transaction_type', TransactionType::$DEBIT);
            }], 'amount')
            ->withSum(['transactions as total_credit' => function($q) use ($advanceHeadId) {
                $q->where('account_head_id', $advanceHeadId)->where('transaction_type', TransactionType::$CREDIT);
            }], 'amount');
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }

    public function advanceBalance()
    {
        $advanceHeadId = AccountHead::where('reserved_id', ReservedAccountHead::EMPLOYEE_ADVANCE)->value('id');
        if (!$advanceHeadId) {
            return 0;
        }

        $totalDebit = Transaction::where('employee_id', $this->id)
            ->where('account_head_id', $advanceHeadId)
            ->where('transaction_type', TransactionType::$DEBIT)
            ->sum('amount');

        $totalCredit = Transaction::where('employee_id', $this->id)
            ->where('account_head_id', $advanceHeadId)
            ->where('transaction_type', TransactionType::$CREDIT)
            ->sum('amount');

        return $totalDebit - $totalCredit;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
    public function employeeStatus()
    {
        return $this->belongsTo(EmployeeStatus::class);
    }
    public function currentDivision() { return $this->belongsTo(Division::class, 'current_division_id'); }
    public function currentDistrict() { return $this->belongsTo(District::class, 'current_district_id'); }
    public function currentUpazila() { return $this->belongsTo(Upazila::class, 'current_upazila_id'); }
    public function currentUnion() { return $this->belongsTo(Union::class, 'current_union_id'); }
    public function currentPourashava() { return $this->belongsTo(Pourashava::class, 'current_pourashava_id'); }
    public function currentVillage() { return $this->belongsTo(Village::class, 'current_village_id'); }
    public function permanentDivision() { return $this->belongsTo(Division::class, 'permanent_division_id'); }
    public function permanentDistrict() { return $this->belongsTo(District::class, 'permanent_district_id'); }
    public function permanentUpazila() { return $this->belongsTo(Upazila::class, 'permanent_upazila_id'); }
    public function permanentUnion() { return $this->belongsTo(Union::class, 'permanent_union_id'); }
    public function permanentPourashava() { return $this->belongsTo(Pourashava::class, 'permanent_pourashava_id'); }
    public function permanentVillage() { return $this->belongsTo(Village::class, 'permanent_village_id'); }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'sister_concern_id');
    }

    public function getImageAttribute()
    {
        return $this->photo;
    }

    public function getEmployeeAttribute()
    {
        return $this;
    }

    public function assignBranch()
    {
        return $this->hasOne(SisterConcern::class, 'branch_manager_id');
    }

    public function division() { return $this->currentDivision(); }
    public function district() { return $this->currentDistrict(); }
    public function upazila() { return $this->currentUpazila(); }
    public function union() { return $this->currentUnion(); }
    public function pourashava() { return $this->currentPourashava(); }
    public function village() { return $this->currentVillage(); }
    public function getAddressAttribute()
    {
        $address = [];
        if ($this->currentVillage) {
            $address[] = $this->currentVillage->name;
        }
        if ($this->currentUnion) {
            $address[] = $this->currentUnion->name;
        } elseif ($this->currentPourashava) {
            $address[] = $this->currentPourashava->name;
        }
        if ($this->currentUpazila) {
            $address[] = $this->currentUpazila->name;
        }
        if ($this->currentDistrict) {
            $address[] = $this->currentDistrict->name;
        }
        return implode(', ', $address);
    }

    public static function generateEmployeeId($branchId)
    {
        $branch = SisterConcern::find($branchId);
        $branchCode = $branch ? $branch->code : '100';

        $lastEmployee = self::withTrashed()
            ->where('sister_concern_id', $branchId)
            ->where('id_no', 'like', $branchCode . '%')
            ->orderBy('id_no', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastEmployee) {
            $lastSequenceStr = str_replace($branchCode, '', $lastEmployee->id_no);
            if (is_numeric($lastSequenceStr)) {
                $nextSequence = (int)$lastSequenceStr + 1;
            }
        }

        return $branchCode . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
    }

    // --- Role Helpers ---
    public function isSR()
    {
        return $this->designation && $this->designation->reserved_id == ReservedDesignation::$SR;
    }

    public function isBranchManager()
    {
        return $this->designation && $this->designation->reserved_id == ReservedDesignation::$BRANCH_MANAGER;
    }

    public function isAreaManager()
    {
        return $this->designation && $this->designation->reserved_id == ReservedDesignation::$AREA_MANAGER;
    }

    public function isFieldOfficer()
    {
        return $this->designation && $this->designation->reserved_id == ReservedDesignation::$FIELD_OFFICER;
    }

    public function isTechnician()
    {
        return $this->designation && $this->designation->reserved_id == ReservedDesignation::$TECHNICIAN;
    }

    public function isTrainee()
    {
        return $this->designation && $this->designation->reserved_id == ReservedDesignation::$TRAINEE;
    }

    public function getGenderTextAttribute()
    {
        $genders = [1 => 'Male', 2 => 'Female', 3 => 'Other'];
        return $this->gender ? ($genders[$this->gender] ?? 'N/A') : 'N/A';
    }

    public function getMaritalStatusTextAttribute()
    {
        $statuses = [1 => 'Married', 2 => 'Single', 3 => 'Widowed', 4 => 'Divorced'];
        return $this->marital_status ? ($statuses[$this->marital_status] ?? 'N/A') : 'N/A';
    }

    public function getPermanentFullAddressAttribute()
    {
        $parts = [
            $this->permanentVillage->name ?? null,
            $this->permanentPourashava->name ?? null,
            $this->permanentUnion->name ?? null,
            $this->permanentUpazila->name ?? null,
            $this->permanentDistrict->name ?? null,
            $this->permanentDivision->name ?? null,
            $this->permanent_address
        ];
        return implode(', ', array_filter($parts));
    }

    public function getCurrentFullAddressAttribute()
    {
        $parts = [
            $this->currentVillage->name ?? null,
            $this->currentPourashava->name ?? null,
            $this->currentUnion->name ?? null,
            $this->currentUpazila->name ?? null,
            $this->currentDistrict->name ?? null,
            $this->currentDivision->name ?? null,
            $this->current_address
        ];
        return implode(', ', array_filter($parts));
    }

    // --- Local Scopes ---
    public function scopeSrs($query)
    {
        return $query->whereHas('designation', function($q) {
            $q->where('reserved_id', ReservedDesignation::$SR);
        });
    }

    public function scopeBranchManagers($query)
    {
        return $query->whereHas('designation', function($q) {
            $q->where('reserved_id', ReservedDesignation::$BRANCH_MANAGER);
        });
    }

    public function scopeAreaManagers($query)
    {
        return $query->whereHas('designation', function($q) {
            $q->where('reserved_id', ReservedDesignation::$AREA_MANAGER);
        });
    }

    public function scopeFieldOfficers($query)
    {
        return $query->whereHas('designation', function($q) {
            $q->where('reserved_id', ReservedDesignation::$FIELD_OFFICER);
        });
    }

    public function scopeTechnicians($query)
    {
        return $query->whereHas('designation', function($q) {
            $q->where('reserved_id', ReservedDesignation::$TECHNICIAN);
        });
    }

    public function scopeTrainees($query)
    {
        return $query->whereHas('designation', function($q) {
            $q->where('reserved_id', ReservedDesignation::$TRAINEE);
        });
    }

    public function cards()
    {
        return $this->morphMany(Card::class, 'cardable');
    }

    public function routePlans()
    {
        return $this->hasMany(RoutePlan::class, 'sr_id');
    }
}

