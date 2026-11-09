<?php

use SebastianPopp\CronOverHttp\CronOverHttpExceptionHandler;
use SebastianPopp\CronOverHttp\Facades\CronOverHttpExceptionHandler as CronOverHttpExceptionHandlerFacade;

it('rethrows the exception by default', function () {
    $handler = new CronOverHttpExceptionHandler;
    $exception = new Exception('boom');

    $handler->handle($exception);
})->throws(Exception::class, 'boom');

it('calls a custom handler instead of rethrowing', function () {
    $received = null;
    $handler = new CronOverHttpExceptionHandler(function (Throwable $exception) use (&$received) {
        $received = $exception;
    });

    $exception = new Exception('boom');
    $handler->handle($exception);

    expect($received)->toBe($exception);
});

it('allows the handler to be replaced after construction', function () {
    $handler = new CronOverHttpExceptionHandler;
    $received = null;

    $handler->setHandler(function (Throwable $exception) use (&$received) {
        $received = $exception;
    });

    $exception = new Exception('boom');
    $handler->handle($exception);

    expect($received)->toBe($exception);
});

it('resolves the exception handler through the facade', function () {
    expect(CronOverHttpExceptionHandlerFacade::getFacadeRoot())
        ->toBeInstanceOf(CronOverHttpExceptionHandler::class);
});
