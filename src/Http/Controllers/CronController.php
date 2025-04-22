<?php

namespace SebastianPopp\CronOverHttp\Http\Controllers;

use Illuminate\Console\Application;
use Illuminate\Http\Request;
use Illuminate\Process\Pool;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Process;
use SebastianPopp\CronOverHttp\Facades\CronOverHttpExceptionHandler;

class CronController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()
            ->stream(function () use ($request) {
                $this->run($request);
            }, 200, [
                'Content-Type' => 'text/plain',
                'X-Accel-Buffering' => 'no',
            ]);
    }

    protected function run(Request $request)
    {
        if ($request->get('secret') !== config('cron-over-http.secret')) {
            abort(403);
        }

        $path = base_path();
        $phpBinary = Application::phpBinary();
        $artisan = Application::artisanBinary();

        $this->print('info', 'Starting jobs...');

        $pool = Process::pool(function (Pool $pool) use ($path, $phpBinary, $artisan) {
            $pool->as('schedule')->path($path)->command($phpBinary.' '.$artisan.' schedule:run');

            if (config('cron-over-http.queue')) {
                $maxTime = (int) config('cron-over-http.max-time');

                $pool->as('queue')->path($path)->command($phpBinary.' '.$artisan.' queue:work --max-time='.$maxTime);
            }
        })->start(function (string $type, string $output, string $key) {
            $this->print($key, $output);
        });

        try {
            $pool->wait();
        } catch (\Throwable $e) {
            CronOverHttpExceptionHandler::handle($e);
        }

        $this->print('info', 'All jobs finished!');
    }

    protected function print($cat, $output)
    {
        $output = trim($output, PHP_EOL);
        $prefix = str('['.strtoupper($cat).']')->padRight(10)->append(' ');

        echo $prefix.implode(PHP_EOL.$prefix, explode(PHP_EOL, $output)).PHP_EOL;

        flush();
    }
}
