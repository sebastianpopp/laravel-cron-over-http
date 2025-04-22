<?php

namespace SebastianPopp\CronOverHttp;

use Closure;

class CronOverHttpExceptionHandler
{
    private Closure $handler;

    public function __construct(?Closure $handler = null)
    {
        if ($handler === null) {
            $handler = fn (\Throwable $exception) => throw $exception;
        }

        $this->setHandler($handler);
    }

    public function setHandler(Closure $handler)
    {
        $this->handler = $handler;
    }

    public function handle(\Throwable $exception)
    {
        call_user_func($this->handler, $exception);
    }
}
