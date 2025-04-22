<?php

namespace SebastianPopp\CronOverHttp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setHandler(\Closure $handler)
 * @method static void handle(\Throwable $exception)
 *
 * @see \SebastianPopp\CronOverHttp\CronOverHttpExceptionHandler
 */
class CronOverHttpExceptionHandler extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cron-over-http.exception-handler';
    }
}
