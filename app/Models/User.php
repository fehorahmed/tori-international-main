<?php

namespace App\Models;

use App\Models\BranchManagement\SisterConcern;
use App\Models\CardManagement\Card;
use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\UserManagement\UserDeviceToken;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,SoftDeletes,HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'mobile_no',
        'employee_id',
        'sister_concern_id',
        'role',
        'status',
        'theme_mode',
        'password',
        'is_online',
        'sr_id',
        'supplier_id',
        'latest_latitude',
        'latest_longitude',
        'otp',
        'otp_expires_at',
    ];

    public function activeConcernId()
    {
        return session('active_sister_concern_id', $this->sister_concern_id);
    }

    public function getActiveSisterConcernAttribute()
    {
        return SisterConcern::find($this->activeConcernId());
    }

    public function getBusinessDateAttribute()
    {
        $concern = $this->active_sister_concern;
        return $concern ? $concern->business_date : date('Y-m-d');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // 'otp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'otp_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(UserDeviceToken::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'created_by');
    }

}

