<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipCashoutSetting extends Model
{
    protected $guarded = [];

    public static function current(): self
    {
        return static::find(1) ?? new static(['id' => 1, 'enabled' => false]);
    }
}
