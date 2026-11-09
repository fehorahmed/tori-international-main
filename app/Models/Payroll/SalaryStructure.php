<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'basic_percent',
        'house_rent_percent',
        'medical_percent',
        'ta_da_fixed',
        'pf_percent',
        'tax_percent',
        'status',
        'created_by'
    ];
}
