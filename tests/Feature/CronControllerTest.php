<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpKernel\Exception\HttpException;

function requestCron(string $uri = '/cron', array $headers = [])
{
    $bufferLevel = ob_get_level();

    try {
        test()->get($uri, $headers)->streamedContent();
    } finally {
        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }
    }
}

beforeEach(function () {
    Config::set('cron-over-http.secret', 'test-secret');
});

it('rejects requests with a missing or incorrect secret', function () {
    Process::fake();

    foreach (['/cron', '/cron?secret=wrong'] as $uri) {
        try {
            requestCron($uri);
            $this->fail('Expected an HttpException to be thrown.');
        } catch (HttpException $e) {
            expect($e->getStatusCode())->toBe(403);
        }
    }

    Process::assertNothingRan();
});

it('rejects requests with an incorrect secret header', function () {
    Process::fake();

    try {
        requestCron('/cron', ['X-Cron-Secret' => 'wrong']);
        $this->fail('Expected an HttpException to be thrown.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
    }

    Process::assertNothingRan();
});

it('accepts the secret via the X-Cron-Secret header', function () {
    Process::fake();

    requestCron('/cron?secret=wrong', ['X-Cron-Secret' => 'test-secret']);

    Process::assertRan(fn ($process) => str_contains($process->command, 'schedule:run'));
});

it('aborts with a server error when no secret is configured', function () {
    Config::set('cron-over-http.secret', null);
    Process::fake();

    try {
        requestCron('/cron?secret=test-secret');
        $this->fail('Expected an HttpException to be thrown.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(500);
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
