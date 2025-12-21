<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Digiflazz price sync every 5 minutes
Schedule::command('digiflazz:sync-prices')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule Item4Gamer order status check every 3 minutes
Schedule::command('item4gamer:check-status')
    ->everyThreeMinutes()
    ->withoutOverlapping()
    ->runInBackground();
