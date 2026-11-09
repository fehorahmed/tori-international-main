<?php

namespace App\Models\StockTransfer;

use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }
    public function fromSisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'from_sister_concern_id');
    }
    public function toSisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'to_sister_concern_id');
    }
    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }
    public function receivedUser()
    {
        return $this->belongsTo(User::class, 'received_user_id');
    }
    public function voucher()
    {
        return $this->hasOne(Voucher::class);
    }
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
    // Parent transfer (for return transfers)
    public function parentTransfer()
    {
        return $this->belongsTo(StockTransfer::class, 'parent_transfer_id');
    }
    // Return transfers created for this transfer (shortages)
    public function returnTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'parent_transfer_id')->where('is_return', 1);
    }
}

