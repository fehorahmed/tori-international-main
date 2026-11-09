<?php

namespace App\Models\ShomriddhiAdvance;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ShomriddhiPassbook extends Model
{
    protected $fillable = [
        'shomriddhi_account_id',
        'passbook_number',
        'status',
        'reason',
        'issued_by'
    ];

    public function account()
    {
        return $this->belongsTo(ShomriddhiAccount::class, 'shomriddhi_account_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
