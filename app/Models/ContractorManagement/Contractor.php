<?php

namespace App\Models\ContractorManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account\AccountHead;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use App\Models\Village;
use App\Models\Account\Transaction;

class Contractor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'origin_sister_concern_id',
        'contractor_type_id',
        'name',
        'nid_number',
        'dob',
        'company_name',
        'email',
        'mobile_no',
        'division_id',
        'district_id',
        'upazila_id',
        'union_id',
        'pourashava_id',
        'village_id',
        'image',
        'address',
        'account_head_id',
        'status',
    ];

    public function contractorType()
    {
        return $this->belongsTo(ContractorType::class, 'contractor_type_id');
    }

    public function accountHead()
    {
        return $this->belongsTo(AccountHead::class, 'account_head_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_head_id', 'account_head_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }

    public function union()
    {
        return $this->belongsTo(Union::class, 'union_id');
    }

    public function pourashava()
    {
        return $this->belongsTo(Pourashava::class, 'pourashava_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function hasTransactions()
    {
        return $this->account_head_id && Transaction::where('account_head_id', $this->account_head_id)->exists();
    }
}
