<?php

namespace App\Models\ContractorManagement;

use Illuminate\Database\Eloquent\Model;

class ContractorType extends Model
{
    protected $fillable = ['name', 'status'];

    public function contractors()
    {
        return $this->hasMany(Contractor::class, 'contractor_type_id');
    }
}
