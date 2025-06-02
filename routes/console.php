<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\TurnOffDevice;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//growlight
Schedule::command('device:toggle DEV014 --on')->dailyAt('7:00');
Schedule::command('device:toggle DEV014 --off')->dailyAt('17:00');

//nutrition pump
// Schedule::command('device:toggle DEV018 --on')->days([1, 3, 5])->at('07:00');
// Schedule::command('device:toggle DEV018 --off')->days([1, 3, 5])->at('07:01');

