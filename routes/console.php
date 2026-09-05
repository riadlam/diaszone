<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Digiflazz price sync every 5 minutes
// Note: do NOT use runInBackground() with withoutOverlapping() — if the
// background process dies before schedule:finish, the mutex sticks for up to
// 24h and later runs are skipped silently (no logs, no Telegram).
Schedule::command('digiflazz:sync-prices')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/digiflazz-sync.log'));

// Schedule Item4Gamer order status check every 3 minutes
Schedule::command('item4gamer:check-status')
    ->everyThreeMinutes()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/item4gamer-status.log'));

// Schedule Digiflazz order status check every 5 minutes
Schedule::command('digiflazz:check-order-status')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/digiflazz-order-status.log'));

// VIP Reseller digital delivery can take ~10+ minutes; poll as webhook backup.
Schedule::command('vipreseller:check-order-status')
    ->everyTwoMinutes()
    ->withoutOverlapping(8)
    ->appendOutputTo(storage_path('logs/vipreseller-order-status.log'));

// Safety net for the lucky wheel: credit any qualifying top-up whose delivery
// was written outside Eloquent or while the wheel credit failed.
Schedule::command('wheel:backfill')
    ->everyTenMinutes()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/wheel-backfill.log'));
