<?php

use App\Events\newHistoryEvent;
use Illuminate\Support\Facades\Route;
use App\Models\Device;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/coba', function () {
    return view('coba');
});

Route::get('/devices/{deviceId}', function ($deviceId) {
    $device = Device::where('deviceId', $deviceId)->firstOrFail();
    return view('device-detail', ['device' => $device]);
});

Route::get('/history', function () {
    event(new newHistoryEvent("test", "test-device-id"));
    return "done";
});