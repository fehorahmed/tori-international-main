<?php

namespace App\Models\ContractorManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account\AccountHead;

class ContractorPayment extends Model
{
    protected $guarded = [];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(AccountHead::class, 'payment_method_id');
    }
}