<?php

namespace App\Models\ContractorManagement;

use Illuminate\Database\Eloquent\Model;

class ContractorBill extends Model
{
    protected $guarded = [];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function details()
    {
        return $this->hasMany(ContractorBillDetail::class);
    }
}