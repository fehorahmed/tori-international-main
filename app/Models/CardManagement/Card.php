<?php

namespace App\Models\CardManagement;

use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'origin_sister_concern_id',
        'card_number',
        'cardable_type',
        'cardable_id',
        'status',
        'amount',
        'payment_status',
        'received_by',
        'received_at',
        'created_by'
    ];

    /**
     * Get the owning cardable model (Supplier or Employee).
     */
    public function cardable()
    {
        return $this->morphTo();
    }


    /**
     * Get the print logs for the card.
     */
    public function logs()
    {
        return $this->hasMany(CardPrintLog::class, 'card_id');
    }

    /**
     * Get the creator of this card.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
