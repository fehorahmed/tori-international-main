<?php

namespace App\Models\SmsManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsGateway extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = ['api_key' => 'encrypted', 'username' => 'encrypted', 'password' => 'encrypted'];

    protected $hidden = ['api_key', 'username', 'password'];
}
