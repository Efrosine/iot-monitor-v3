<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('device:toggle DEV014 --on')->dailyAt('10:00');
Schedule::command('device:toggle DEV014 --off')->dailyAt('18:00');