# Laravel Cron over HTTP

Run your Laravel cron jobs and queue over HTTP.

## Installation

You can install the package via composer:

```bash
composer require sebastianpopp/laravel-cron-over-http
```

Define a secret token in your `.env` file:

```bash
CRON_SECRET="your-secret-token"
```

Setup a cron job to call the `cron` route:

```
https://your-app.com/cron?secret=your-secret-token
```

You can optionally disable the queue by setting the `CRON_QUEUE` environment variable to `false`:

```bash
CRON_QUEUE=false
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
