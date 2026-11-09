<?php

namespace App\Models\CardManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CardPrintLog extends Model
{
    protected $table = 'card_print_logs';

    protected $fillable = [
        'origin_sister_concern_id',
        'card_id',
        'printed_by',
        'printed_at',
        'print_reason',
        'notes'
    ];

    protected $casts = [
        'printed_at' => 'datetime'
    ];

    /**
     * Get the card associated with the print log.
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    /**
     * Get the user who printed this card.
     */
    public function printer()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
