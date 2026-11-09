<?php

namespace App\Models\Account;

use App\Models\Sales\Service\Service;
use App\Models\StockConversion\StockConversion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];
    public function paymentAccountHead()
    {
        return $this->belongsTo(AccountHead::class,'payment_account_head_id');
    }
    public function payeeDepositorHead()
    {
        return $this->belongsTo(AccountHead::class,'account_head_payee_depositor_id');
    }
    public function accountHead()
    {
        return $this->belongsTo(AccountHead::class);
    }
    public function sourceAccountHead()
    {
        return $this->belongsTo(AccountHead::class,'source_account_head_id');
    }
    public function targetAccountHead()
    {
        return $this->belongsTo(AccountHead::class,'target_account_head_id');
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function stockConversion()
    {
        return $this->belongsTo(StockConversion::class);
    }
}
