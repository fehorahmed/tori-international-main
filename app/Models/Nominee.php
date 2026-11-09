<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nominee extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;
    protected $guarded = [];
}
