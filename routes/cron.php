<?php

use Illuminate\Support\Facades\Route;

Route::get(
    config('cron-over-http.route', '/cron'),
    \SebastianPopp\CronOverHttp\Http\Controllers\CronController::class
)->name('cron-over-http');
