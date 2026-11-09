<?php

namespace App\Models\ShomriddhiDps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpsScheme extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function accounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DpsAccount::class, 'scheme_id');
    }

    public function activeAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DpsAccount::class, 'scheme_id')->where('status', 'Active');
    }
}
