<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PayloadController;
use App\Http\Controllers\ActuatorsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Device routes - full CRUD
Route::apiResource('devices', DeviceController::class);
// Toggle auto mode for a device
Route::put('/devices/{deviceId}/auto-mode', [DeviceController::class, 'toggleAutoMode'])->where('deviceId', '[A-Za-z0-9\-]+');

// Payload routes - read and update
Route::get('/payloads', [PayloadController::class, 'index']);
Route::get('/payloads/{deviceId}', [PayloadController::class, 'show'])->where('deviceId', '[A-Za-z0-9\-]+');
Route::put('/payloads/{deviceId}', [PayloadController::class, 'update']);
Route::post('/payloads/{deviceId}', [PayloadController::class, 'store'])->where('deviceId', '[A-Za-z0-9\-]+');
// Payload history route - get last entries (default 60)
Route::get('/payloads/{deviceId}/history/{limit?}', [PayloadController::class, 'history'])
    ->where('deviceId', '[A-Za-z0-9\-]+')
    ->where('limit', '[0-9]+');


// Actuator routes
Route::prefix('actuators')->group(function () {
    // Get all actuators
    Route::get('/', [ActuatorsController::class, 'index']);

    // Payloads for all actuators
    Route::get('/payloads', [ActuatorsController::class, 'payloads']);

    // Get specific actuator
    Route::get('/{id}', [ActuatorsController::class, 'show']);
});