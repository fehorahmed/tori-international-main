<?php

namespace App\Support;

use Carbon\Carbon;

final class ApplicationDate
{
    public static function today(): string
    {
        return Carbon::now('Asia/Dhaka')->toDateString();
    }
}
