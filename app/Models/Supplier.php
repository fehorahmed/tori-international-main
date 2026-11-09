<?php

namespace App\Models;

use App\Enumeration\SaleType;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\Transaction;
use App\Models\CardManagement\Card;
use App\Models\Microfinance\MfMember;
use App\Models\RoutePlan\RoutePlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Account\AccountHead;
use App\Models\Purchase\PurchaseOrder;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Sales\SaleOrder;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;

class Supplier extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;
    protected $guarded = [];
    
    protected $hidden = [
        'password',
        'pin',
        'biometric_token',
        'remember_token',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }

    public function union()
    {
        return $this->belongsTo(Union::class, 'union_id');
    }

    public function pourashava()
    {
        return $this->belongsTo(Pourashava::class, 'pourashava_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }
    public function originSisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'origin_sister_concern_id');
    }
    public function assignBranch(){
        return $this->belongsTo(SisterConcern::class,'id','branch_manager_id');
    }
    
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function mfMember()
    {
        return $this->hasOne(MfMember::class, 'supplier_id');
    }

    public function getOpeningBalanceAttribute()
    {
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $this->id)->first();
        if ($accountHead) {
            return $accountHead->getOpeningBalance(auth()->user()->activeConcernId());
        }
        return 0;
    }
    public function getTotalCustomerPaidAttribute()
    {
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $this->id)->first();
        if (!$accountHead) return 0;

        return Transaction::where('account_head_id', $accountHead->id)
            ->where('transaction_type', TransactionType::$CREDIT) // For customer, receipt is Credit
            ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
            ->sum('amount');
    }

    public function getCustomerCurrentBalanceAttribute()
    {
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $this->id)->first();
        if (!$accountHead) return 0;
        
        $debit = Transaction::where('account_head_id', $accountHead->id)
            ->where('transaction_type', TransactionType::$DEBIT)
            ->sum('amount');
            
        $credit = Transaction::where('account_head_id', $accountHead->id)
            ->where('transaction_type', TransactionType::$CREDIT)
            ->sum('amount');
            
        // Customer is an Asset (Accounts Receivable) -> Debit nature (Debit - Credit)
        return $debit - $credit;
    }

    public function getTotalSupplierPaidAttribute()
    {
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $this->id)->first();
        if (!$accountHead) return 0;

        return Transaction::where('account_head_id', $accountHead->id)
            ->where('transaction_type', TransactionType::$DEBIT)
            ->whereIn('voucher_type', [VoucherType::$PAYMENT_VOUCHER, VoucherType::$JOURNAL_VOUCHER])
            ->where('is_opening_balance', 0)
            ->sum('amount');
    }
    
    public function getTotalSupplierReceivedAttribute()
    {
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $this->id)->first();
        if (!$accountHead) return 0;
        
        return Transaction::where('account_head_id', $accountHead->id)
            ->where('transaction_type', TransactionType::$CREDIT)
            ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
            ->sum('amount');
    }

    public function getSupplierCurrentBalanceAttribute()
    {
        $opening_balance = $this->opening_balance ?? 0;
        $totalPurchase = $this->purchase_orders_sum_total ?? $this->purchaseOrders()->sum('total');
        $totalPaid = $this->total_supplier_paid;
        $totalReceived = $this->total_supplier_received;
            
        return $opening_balance + $totalPurchase + $totalReceived - $totalPaid;
    }

    public function hasPendingPreOrder()
    {
        // Check if the global customer has any pending pre-order in the active branch
        return SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('customer_id', $this->id)
            ->where('type', SaleType::$PRE_ORDER)
            ->where('status', 1)
            ->exists();
    }

    public function routePlans()
    {
        return $this->hasMany(RoutePlan::class, 'sr_id');
    }

    public function cards()
    {
        return $this->morphMany(Card::class, 'cardable');
    }

    public function account_heads()
    {
        return $this->hasMany(AccountHead::class, 'supplier_id');
    }

    public function shomriddhiAccount()
    {
        return $this->hasOne(ShomriddhiAccount::class, 'customer_id');
    }

    public function hasShomriddhiAccount()
    {
        return $this->shomriddhiAccount()->exists();
    }

    public function shomriddhiGroup()
    {
        return $this->belongsTo(\App\Models\ShomriddhiAdvance\ShomriddhiGroup::class, 'shomriddhi_group_id');
    }
}
