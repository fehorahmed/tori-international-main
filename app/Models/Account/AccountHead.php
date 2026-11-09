<?php

namespace App\Models\Account;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountHead extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class);
    }
    public function openingTransactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function getOpeningBalance($sisterConcernId = null)
    {
        $query = $this->transactions()->where('is_opening_balance', 1);
        if ($sisterConcernId) {
            $query->where('sister_concern_id', $sisterConcernId);
        }
        
        $result = $query->selectRaw('
            SUM(CASE WHEN transaction_type = 1 THEN amount ELSE 0 END) as debit,
            SUM(CASE WHEN transaction_type = 2 THEN amount ELSE 0 END) as credit
        ')->first();

        $debit = $result->debit ?? 0;
        $credit = $result->credit ?? 0;

        return $this->nature == 'Debit' ? ($debit - $credit) : ($credit - $debit);
    }

    public function getCurrentBalance($sisterConcernId = null)
    {
        $query = $this->transactions();
        if ($sisterConcernId) {
            $query->where('sister_concern_id', $sisterConcernId);
        }
        
        $debit = (clone $query)->where('transaction_type', 1)->sum('amount');
        $credit = (clone $query)->where('transaction_type', 2)->sum('amount');

        return $this->nature == 'Debit' ? ($debit - $credit) : ($credit - $debit);
    }

    public function isSufficient($amount, $sisterConcernId = null)
    {
        // For Cash (2) and Bank (1), Mobile Banking (3) we check for sufficient balance.
        if (in_array($this->payment_mode, [1, 2, 3])) {
            return $this->getCurrentBalance($sisterConcernId) >= $amount;
        }
        return true;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->with('payeeDepositorHead');
    }
}
