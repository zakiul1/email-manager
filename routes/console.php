<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sendportal:dispatch-scheduled-campaigns')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('sendportal:retry-failed-messages')
    ->everyFiveMinutes()
    ->withoutOverlapping();