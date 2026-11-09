<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
