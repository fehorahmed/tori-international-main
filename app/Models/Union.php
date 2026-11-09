<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Union extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }
}
