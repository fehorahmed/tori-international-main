<?php

namespace App\Models\ShomriddhiAdvance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Supplier;
use App\Models\User;

class ShomriddhiTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'shomriddhi_transactions';

    protected $fillable = [
        'customer_id',
        'type',
        'amount',
        'date',
        'note',
        'is_opening_balance',
        'status',
        'voucher_id',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    public function customer()
    {
        return $this->belongsTo(Supplier::class, 'customer_id'); // Assuming Supplier is used for Customer
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
