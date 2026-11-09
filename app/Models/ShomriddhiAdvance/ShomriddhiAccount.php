<?php

namespace App\Models\ShomriddhiAdvance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Supplier;

class ShomriddhiAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_number',
        'customer_id',
        'origin_sister_concern_id',
        'opening_date',
        'principal_balance',
        'accrued_interest',
        'last_interest_date',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id');
    }

    public function nominees()
    {
        return $this->hasMany(ShomriddhiNominee::class, 'account_id')->where('account_type', 'Savings');
    }

    public function passbooks()
    {
        return $this->hasMany(ShomriddhiPassbook::class);
    }

    public function activePassbook()
    {
        return $this->hasOne(ShomriddhiPassbook::class)->where('status', 'Active');
    }
}
