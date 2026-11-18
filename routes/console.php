<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('membership:generate-monthly-invoices')->dailyAt('23:00')->when(fn () => now()->isLastOfMonth())->withoutOverlapping();

Schedule::command('membership:generate-yearly-invoices')->dailyAt('23:10')->withoutOverlapping();