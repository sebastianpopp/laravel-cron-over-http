<?php

// config for SebastianPopp/CronOverHttp
return [
    'route' => '/cron',

    'secret' => env('CRON_SECRET', null),

    'queue' => env('CRON_QUEUE', true),

    'max-time' => env('CRON_MAX_TIME', 50),
];
