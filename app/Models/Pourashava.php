<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pourashava extends Model
{
    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
