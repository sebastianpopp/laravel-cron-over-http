<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Config::set('cron-over-http.secret', 'test-secret');
});

it('rejects requests with a missing or incorrect secret', function () {
    Process::fake();

    foreach (['/cron', '/cron?secret=wrong'] as $uri) {
        $bufferLevel = ob_get_level();

        try {
            $this->get($uri)->streamedContent();
            $this->fail('Expected an HttpException to be thrown.');
        } catch (HttpException $e) {
            expect($e->getStatusCode())->toBe(403);
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }
    }

    Process::assertNothingRan();
});

it('runs the schedule and queue processes when the secret matches', function () {
    Process::fake();

    $response = $this->get('/cron?secret=test-secret');
    $response->assertOk();

    $content = $response->streamedContent();

    expect($content)
        ->toContain('Starting jobs...')
        ->toContain('All jobs finished!');

    Process::assertRan(fn ($process) => str_contains($process->command, 'schedule:run'));
    Process::assertRan(fn ($process) => str_contains($process->command, 'queue:work'));
});

it('skips the queue worker when the queue is disabled', function () {
    Config::set('cron-over-http.queue_enabled', false);
    Process::fake();

    $this->get('/cron?secret=test-secret')->streamedContent();

    Process::assertRan(fn ($process) => str_contains($process->command, 'schedule:run'));
    Process::assertNotRan(fn ($process) => str_contains($process->command, 'queue:work'));
});
