<?php

namespace App\Models\OfficeManagement;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'sister_concern_id',
        'title',
        'content',
        'meeting_date',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];
}
