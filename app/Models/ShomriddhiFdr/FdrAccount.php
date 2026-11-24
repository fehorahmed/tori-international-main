<?php

namespace App\Models\ShomriddhiFdr;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FdrAccount extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function scheme()
    {
        return $this->belongsTo(FdrScheme::class, 'scheme_id');
    }

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function transactions()
    {
        return $this->hasMany(FdrTransaction::class, 'fdr_account_id');
    }

    public function nominees()
    {
        return $this->hasMany(FdrNominee::class, 'fdr_account_id');
    }
}
