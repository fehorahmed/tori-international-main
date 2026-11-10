<?php

namespace App\Models\ShomriddhiAdvance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Supplier;

class ShomriddhiAccount extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::updating(function (self $account) {
            if ($account->isDirty('status') && $account->status !== 'Active' && \App\Models\MembershipCashout::held($account->id) > 0) {
                \App\Services\Membership\CashoutService::fail('Close cash-out requests before closing this savings account.');
            }
            if ($account->isDirty('principal_balance')) {
                $current = static::whereKey($account->id)->lockForUpdate()->firstOrFail();
                if (\App\Services\Membership\CashoutService::minor($current->principal_balance)
                    !== \App\Services\Membership\CashoutService::minor($account->getRawOriginal('principal_balance'))) {
                    \App\Services\Membership\CashoutService::fail('Savings balance changed. Refresh and retry.', 'সঞ্চয় ব্যালেন্স পরিবর্তন হয়েছে। রিফ্রেশ করে আবার চেষ্টা করুন।');
                }
                $balance = \App\Services\Membership\CashoutService::minor($account->principal_balance);
                if ($balance < \App\Models\MembershipCashout::held($account->id)) {
                    \App\Services\Membership\CashoutService::fail('This savings balance is reserved for a cash-out request.', 'এই সঞ্চয়ের টাকা ক্যাশ আউট অনুরোধের জন্য সংরক্ষিত আছে।');
                }
            }
        });
        static::deleting(function (self $account) {
            if (\App\Models\MembershipCashout::held($account->id) > 0) {
                \App\Services\Membership\CashoutService::fail('Close cash-out requests before deleting this savings account.');
            }
        });
    }

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
