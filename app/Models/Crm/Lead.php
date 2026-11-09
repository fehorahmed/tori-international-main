<?php

namespace App\Models\Crm;

use App\Models\Hrm\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function crmClient()
    {
        return $this->belongsTo(CrmClient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
