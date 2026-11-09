<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

// Schedule::command('database:backup --force')->daily();
// Schedule::command('mf:daily-cron')->daily();

// Keep only the attendance reminder cron job running for now
// Schedule::command('attendance:check-hourly')->everyMinute(); // পুরনো
Schedule::command('attendance:check-hourly')->everyFiveMinutes();

// Shomriddhi Advance Crons
// সার্ভার ৫ মিনিট পর পর cron চালায়, তাই dailyAt() miss হতে পারে।
// between() দিয়ে time window দেওয়া হয়েছে — এই window এর মধ্যে
// যেকোনো একটি run-এ execute হবে। withoutOverlapping() নিশ্চিত করে
// একই সময়ে দুইটি instance চলবে না। command এর ভেতরে last_interest_date
// check থাকায় প্রতিদিন সর্বোচ্চ একবারই interest add হবে।

// Schedule::command('shomriddhi:accrue-daily-interest')->dailyAt('00:05'); // পুরনো
Schedule::command('shomriddhi:accrue-daily-interest')
    ->everyFiveMinutes()
    ->between('23:00', '23:20')
    ->withoutOverlapping();

// Schedule::command('shomriddhi:capitalize-monthly-interest')->dailyAt('01:00'); // পুরনো
Schedule::command('shomriddhi:capitalize-monthly-interest')
    ->everyFiveMinutes()
    ->between('23:20', '23:40')
    ->when(fn () => now()->isLastDayOfMonth())
    ->withoutOverlapping();

// Shomriddhi FDR Crons
// Schedule::command('shomriddhi-fdr:process-interest')->dailyAt('01:30'); // পুরনো
Schedule::command('shomriddhi-fdr:process-interest')
    ->everyFiveMinutes()
    ->between('23:30', '23:45')
    ->withoutOverlapping();

// Shomriddhi DPS Crons
// Schedule::command('shomriddhi-dps:check-missed-installments')->dailyAt('02:00'); // পুরনো
Schedule::command('shomriddhi-dps:check-missed-installments')
    ->everyFiveMinutes()
    ->between('23:40', '23:59')
    ->withoutOverlapping();
