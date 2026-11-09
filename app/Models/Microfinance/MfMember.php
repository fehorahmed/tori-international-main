<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use App\Models\District;
use App\Models\Division;
use App\Models\Pourashava;
use App\Models\Supplier;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\Auditable;

class MfMember extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
    ];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'sister_concern_id');
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function nominees()
    {
        return $this->hasMany(MfNominee::class, 'mf_member_id');
    }

    public function accounts()
    {
        return $this->hasMany(MfSavingsAccount::class, 'member_id');
    }

    public function savingsAccounts()
    {
        return $this->hasMany(MfSavingsAccount::class, 'member_id');
    }

    public function loans()
    {
        return $this->hasMany(MfLoan::class, 'member_id');
    }

    public function somiti()
    {
        return $this->belongsTo(MfSomiti::class, 'somiti_id');
    }

    // Location Relationships
    public function presentDivision() { return $this->belongsTo(Division::class, 'present_division_id'); }
    public function presentDistrict() { return $this->belongsTo(District::class, 'present_district_id'); }
    public function presentUpazila() { return $this->belongsTo(Upazila::class, 'present_upazila_id'); }
    public function presentUnion() { return $this->belongsTo(Union::class, 'present_union_id'); }
    public function presentPourashava() { return $this->belongsTo(Pourashava::class, 'present_pourashava_id'); }
    public function presentVillage() { return $this->belongsTo(Village::class, 'present_village_id'); }


    public function permanentDivision() { return $this->belongsTo(Division::class, 'permanent_division_id'); }
    public function permanentDistrict() { return $this->belongsTo(District::class, 'permanent_district_id'); }
    public function permanentUpazila() { return $this->belongsTo(Upazila::class, 'permanent_upazila_id'); }
    public function permanentUnion() { return $this->belongsTo(Union::class, 'permanent_union_id'); }
    public function permanentPourashava() { return $this->belongsTo(Pourashava::class, 'permanent_pourashava_id'); }
    public function permanentVillage() { return $this->belongsTo(Village::class, 'permanent_village_id'); }

    public function getFullPresentAddressAttribute()
    {
        $parts = [
            $this->present_house_road,
            $this->presentVillage?->name,
            $this->presentUnion?->name,
            $this->presentPourashava?->name,
            $this->presentUpazila?->name,
            $this->presentDistrict?->name,
            $this->presentDivision?->name,
        ];
        return implode(', ', array_filter($parts));
    }

    public function getFullPermanentAddressAttribute()
    {
        $parts = [
            $this->permanent_house_road,
            $this->permanentVillage?->name,
            $this->permanentUnion?->name,
            $this->permanentPourashava?->name,
            $this->permanentUpazila?->name,
            $this->permanentDistrict?->name,
            $this->permanentDivision?->name,
        ];
        return implode(', ', array_filter($parts));
    }
}
