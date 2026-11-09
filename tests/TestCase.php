<?php

namespace SebastianPopp\CronOverHttp\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SebastianPopp\CronOverHttp\CronOverHttpServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            CronOverHttpServiceProvider::class,
        ];
    }
}
