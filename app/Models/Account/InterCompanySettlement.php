<?php

namespace App\Models\Account;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterCompanySettlement extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function fromSisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'from_sister_concern_id');
    }

    public function toSisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'to_sister_concern_id');
    }

    public function fromAccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'from_account_head_id');
    }

    public function toAccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'to_account_head_id');
    }

    public function senderVoucher()
    {
        return $this->belongsTo(Voucher::class, 'sender_voucher_id');
    }

    public function receiverVoucher()
    {
        return $this->belongsTo(Voucher::class, 'receiver_voucher_id');
    }
}
