<?php

namespace App\Models\Account;

use App\Models\Purchase\PurchaseOrder;
use App\Models\Sales\Service\Service;
use App\Models\StockConversion\StockConversion;
use App\Models\ReturnsManagement\SalesReturn;
use App\Models\ReturnsManagement\PurchaseReturn;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Payroll\EmployeeAdvance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function paymentAccountHead()
    {
        return $this->belongsTo(AccountHead::class,'payment_account_head_id');
    }
    public function payeeDepositorAccountHead()
    {
        return $this->belongsTo(AccountHead::class,'account_head_payee_depositor_id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function saleOrder()
    {
        return $this->belongsTo(\App\Models\Sales\SaleOrder::class);
    }
    
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function stockConversion()
    {
        return $this->belongsTo(StockConversion::class);
    }

    public function salesReturn()
    {
        return $this->hasOne(SalesReturn::class, 'return_voucher_id');
    }

    public function purchaseReturn()
    {
        return $this->hasOne(PurchaseReturn::class, 'return_voucher_id');
    }

    public function shomriddhiTransaction()
    {
        return $this->hasOne(ShomriddhiTransaction::class, 'voucher_id');
    }

    public function employeeAdvance()
    {
        return $this->hasOne(EmployeeAdvance::class, 'voucher_no', 'voucher_no');
    }

    public function is_locked()
    {
        if ($this->isMembershipCashout() || $this->isMembershipTransfer()) return true;
        return 
            !empty($this->sale_order_id) || 
            !empty($this->purchase_order_id) || 
            !empty($this->service_id) || 
            !empty($this->stock_conversion_id) || 
            !empty($this->stock_transfer_id) || 
            !empty($this->product_utilize_id) || 
            !empty($this->manufacture_finished_goods_id) || 
            !empty($this->company_loan_id) || 
            $this->salesReturn()->exists() ||
            $this->purchaseReturn()->exists() ||
            $this->shomriddhiTransaction()->exists() ||
            $this->employeeAdvance()->exists();
    }

    public function isPackageSale(): bool
    {
        return $this->sale_order_id && \App\Models\Ecommerce\PackageOrderCheckout::where('sale_order_id', $this->sale_order_id)->exists();
    }

    public function isMembershipTransfer(): bool
    {
        return \App\Models\MembershipTransfer::where('voucher_id', $this->id)->orWhere('recipient_voucher_id', $this->id)->orWhere('fee_voucher_id', $this->id)->exists();
    }

    public function isMembershipCashout(): bool
    {
        return \App\Models\MembershipCashout::where('voucher_id', $this->id)->orWhere('fee_voucher_id', $this->id)->orWhere('source_voucher_id', $this->id)->exists();
    }
}
