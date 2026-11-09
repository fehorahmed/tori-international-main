<?php

namespace App\Models\ShomriddhiAdvance;

use Illuminate\Database\Eloquent\Model;

class ShomriddhiInterestSlab extends Model
{
    protected $fillable = [
        'min_amount',
        'max_amount',
        'interest_rate',
    ];
}
