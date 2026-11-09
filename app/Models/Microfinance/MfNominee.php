<?php

namespace App\Models\Microfinance;

use App\Models\District;
use App\Models\Division;
use App\Models\Pourashava;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MfNominee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(MfMember::class, 'mf_member_id');
    }

    public function division() { return $this->belongsTo(Division::class, 'division_id'); }
    public function district() { return $this->belongsTo(District::class, 'district_id'); }
    public function upazila() { return $this->belongsTo(Upazila::class, 'upazila_id'); }
    public function union() { return $this->belongsTo(Union::class, 'union_id'); }
    public function pourashava() { return $this->belongsTo(Pourashava::class, 'pourashava_id'); }
    public function village() { return $this->belongsTo(Village::class, 'village_id'); }

    public function getFullAddressAttribute()
    {
        $parts = [
            $this->house_road,
            $this->village?->name,
            $this->union?->name,
            $this->pourashava?->name,
            $this->upazila?->name,
            $this->district?->name,
            $this->division?->name,
        ];
        return implode(', ', array_filter($parts));
    }
}
