<?php

namespace App\Models\ContractorManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account\AccountHead;

class ContractorBillDetail extends Model
{
    protected $guarded = [];

    public function expenseHead()
    {
        return $this->belongsTo(AccountHead::class, 'expense_head_id');
    }
}