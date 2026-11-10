<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipTransferSetting extends Model
{
    protected $guarded = [];

    protected $casts = ['enabled' => 'boolean'];

    public static function current(): self
    {
        return static::find(1) ?? new static(['enabled' => false, 'maximum_amount' => '0.00', 'charge_type' => 'fixed', 'charge_rate' => '0.00']);
    }
}
