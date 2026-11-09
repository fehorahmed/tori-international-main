<?php

namespace App\Models\ShomriddhiLoan;

use App\Models\Supplier;
use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanAccount extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'origin_sister_concern_id');
    }

    public function installments()
    {
        return $this->hasMany(LoanInstallment::class, 'loan_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(LoanTransaction::class, 'loan_account_id');
    }
}
