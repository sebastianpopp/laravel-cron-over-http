# Laravel Cron over HTTP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sebastianpopp/laravel-cron-over-http.svg?style=flat-square)](https://packagist.org/packages/sebastianpopp/laravel-cron-over-http)
[![Tests](https://img.shields.io/github/actions/workflow/status/sebastianpopp/laravel-cron-over-http/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sebastianpopp/laravel-cron-over-http/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/sebastianpopp/laravel-cron-over-http.svg?style=flat-square)](https://packagist.org/packages/sebastianpopp/laravel-cron-over-http)
[![Plumb score](https://plumbphp.dev/badges/sebastianpopp/laravel-cron-over-http/composite.svg)](https://plumbphp.dev/sebastianpopp/laravel-cron-over-http)

Run your Laravel cron jobs and queue over HTTP.

This is useful on hosting setups where you can't schedule a real cron job but can hit a URL
periodically (e.g. an external uptime/cron service). Each request runs `schedule:run` and,
optionally, a `queue:work` worker, and streams their output back as plain text.

## Installation

You can install the package via composer:

```bash
composer require sebastianpopp/laravel-cron-over-http
```

Define a secret token in your `.env` file:

```bash
CRON_SECRET="your-secret-token"
```

The route refuses every request until `CRON_SECRET` is set — there is no "unprotected" mode.

Set up a cron job (or external scheduler) to call the `cron` route, passing the secret either as
a query parameter:

```
https://your-app.com/cron?secret=your-secret-token
```

or as an `X-Cron-Secret` header, which is preferable since it won't end up in access logs or
`Referer` headers the way a query string can:

```
GET https://your-app.com/cron
X-Cron-Secret: your-secret-token
```

## Configuration

You can optionally publish the config file:

```bash
php artisan vendor:publish --tag="cron-over-http-config"
```

This is the contents of the published config file:

```php
return [
    // The path the package route is registered on.
    'route' => '/cron',

    // The secret required to call the route. The route always rejects requests if this is empty.
    'secret' => env('CRON_SECRET', null),

    // Whether a queue worker should be started alongside the scheduler.
    'queue_enabled' => env('CRON_QUEUE', true),

    // Passed to `queue:work` as --max-time, so the worker stops instead of running forever.
    'max_time' => env('CRON_MAX_TIME', 50),

    // The queue the worker should process.
    'queue' => env('CRON_QUEUE_NAME', 'default'),
];
```

You can optionally disable the queue by setting the `CRON_QUEUE` environment variable to `false`:

```bash
CRON_QUEUE=false
```

### Shared hosting

The package resolves the PHP binary to use via Laravel's `Illuminate\Console\Application::phpBinary()`,
which relies on the `PHP_BINARY` environment variable. On some shared hosting providers (e.g.
All-Inkl) this isn't set correctly for the web server process, which causes `schedule:run` and
`queue:work` to be launched with the wrong PHP version or to fail outright. You can fix this by
setting it explicitly, for example in your `.htaccess`:

```apache
SetEnv PHP_BINARY /usr/bin/php84
```

### Request timeout

The HTTP request stays open for as long as `schedule:run` and `queue:work --max-time=...` take to
finish, since their output is streamed back as the response. Make sure your web server, PHP
(`max_execution_time`), and whatever is calling the route all allow requests to run at least a
little longer than `CRON_MAX_TIME`, or the process may be killed before it can shut down cleanly.

### Handling exceptions

By default, any exception thrown while running the scheduler or queue worker is rethrown. Since
this happens inside an already-started streamed response, that's rarely what you want in
production — you probably want to report it (e.g. to Sentry/Flare) instead of letting it bubble
up. You can replace the behaviour with your own handler, for example in a service provider's
`boot` method:

```php
use SebastianPopp\CronOverHttp\Facades\CronOverHttpExceptionHandler;

CronOverHttpExceptionHandler::setHandler(function (Throwable $exception) {
    report($exception);
});
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
