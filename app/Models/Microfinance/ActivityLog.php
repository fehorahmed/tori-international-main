<?php

namespace App\Models\Microfinance;

use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function loggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }
}
