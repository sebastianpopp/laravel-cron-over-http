<?php

// config for SebastianPopp/CronOverHttp
return [
    'route' => '/cron',

    'secret' => env('CRON_SECRET', null),

    'queue_enabled' => env('CRON_QUEUE', true),

    'max_time' => env('CRON_MAX_TIME', 50),

    'queue' => env('CRON_QUEUE_NAME', 'default'),
];
