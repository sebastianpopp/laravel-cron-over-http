<?php

use Illuminate\Support\Facades\Route;
use SebastianPopp\CronOverHttp\Http\Controllers\CronController;

Route::get(
    config('cron-over-http.route', '/cron'),
    CronController::class
)->name('cron-over-http');
