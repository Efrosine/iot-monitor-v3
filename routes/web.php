<?php

use App\Events\newHistoryEvent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/history', function () {
    event(new newHistoryEvent());
    return "done";
});