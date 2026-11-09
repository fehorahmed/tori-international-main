<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\Auditable;

class MfSavingsAccount extends Model
{
    use SoftDeletes, Auditable;

    protected $guarded = [];

    protected $casts = [
        'opening_date' => 'date',
        'maturity_date' => 'date',
        'closed_date' => 'date',
        'auto_renewal' => 'boolean',
    ];

    public function introducer()
    {
        return $this->belongsTo(MfMember::class, 'introducer_id');
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function nominee()
    {
        return $this->belongsTo(MfNominee::class, 'nominee_id');
    }

    public function nominees()
    {
        return $this->belongsToMany(MfNominee::class, 'mf_savings_account_nominee', 'mf_savings_account_id', 'mf_nominee_id')
                    ->withPivot('share_percentage')
                    ->withTimestamps();
    }

    public function somiti()
    {
        return $this->belongsTo(MfSomiti::class, 'somiti_id');
    }

    public function member()
    {
        return $this->belongsTo(MfMember::class, 'member_id');
    }

    public function product()
    {
        return $this->belongsTo(MfSavingsProduct::class, 'mf_savings_product_id');
    }

    public function transactions()
    {
        return $this->hasMany(MfSavingsTransaction::class, 'mf_savings_account_id');
    }
}
