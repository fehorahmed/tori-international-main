<?php

namespace App\Services\Membership;

use Carbon\Carbon;

class TransactionWindowService
{
    public static function status(): array
    {
        $s = membership_transaction_settings('transaction_window');
        $now = Carbon::now('Asia/Dhaka');
        $start = (string) $s->start_time;
        $end = (string) $s->end_time;
        $time = $now->format('H:i');
        $blocked = (bool) $s->enabled && ($start < $end ? ($time >= $start && $time < $end) : ($time >= $start || $time < $end));
        $opens = null;
        if ($blocked) {
            $opens = $now->copy()->setTimeFromTimeString($end);
            if ($opens->lte($now)) {
                $opens->addDay();
            }
        }

        return ['enabled' => (bool) $s->enabled, 'blocked' => $blocked, 'start_time' => $start, 'end_time' => $end,
            'timezone' => 'Asia/Dhaka', 'opens_at' => $opens?->toIso8601String(),
            'message' => $blocked ? "Send Money and Member Cash-in are closed until {$opens->format('d M, h:i A')} (Bangladesh time)." : null];
    }

    public static function ensureOpen(): void
    {
        $s = self::status();
        if ($s['blocked']) {
            CashoutService::fail($s['message'], 'সেন্ড মানি ও মেম্বার ক্যাশ-ইন বন্ধ আছে। পুনরায় চালু হবে: '.Carbon::parse($s['opens_at'])->format('d-m-Y h:i A').' (বাংলাদেশ সময়)।');
        }
    }
}
