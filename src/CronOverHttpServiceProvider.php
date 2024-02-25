<?php

namespace SebastianPopp\CronOverHttp;

use Illuminate\Support\ServiceProvider;

class CronOverHttpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cron-over-http.php', 'cron-over-http'
        );

        $this->publishes([
            __DIR__.'/../config/cron-over-http.php' => config_path('cron-over-http.php'),
        ], 'cron-over-http-config');

        $this->loadRoutesFrom(__DIR__.'/../routes/cron.php');
    }
}
