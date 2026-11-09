<?php

namespace App\Models\UserManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserDeviceToken extends Model
{
    protected $fillable = ['user_id', 'token', 'device_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
