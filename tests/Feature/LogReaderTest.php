<?php

use App\Services\LogReader;

afterEach(function (): void {
    deleteTemporaryLogs();
});

/**
 * Every entry of a log, following the `before` cursor from the newest page to the oldest.
 *
 * @return list<array{level: string|null, environment: string|null, date: string|null, message: string, details: string}>
 */
function allLogEntries(string $path, ?string $minimumLevel = null, ?string $search = null): array
{
    $reader = new LogReader;
    $entries = [];
    $before = null;

    do {
        $page = $reader->entries($path, $before, $minimumLevel, $search);
        $entries = [...$entries, ...$page['entries']];
        $before = $page['before'];
    } while ($before !== null);

    return $entries;
}

describe('files', function (): void {
    it('lists the log files with their sizes, leaving out anything that is not a log', function (): void {
        // Given
        useTemporaryLogs(['laravel.log' => 'abc', 'ssr.log' => '', '.gitignore' => '*']);

        // When
        $files = (new LogReader)->files();

        // Then
        expect(array_column($files, 'name'))->toBe(['laravel.log', 'ssr.log'])
            ->and(array_column($files, 'size'))->toBe([3, 0]);
    });
});

describe('path', function (): void {
    it('resolves listed log files only', function (string $name): void {
        // Given
        useTemporaryLogs(['laravel.log' => '', '.gitignore' => '*']);

        // When
        $path = (new LogReader)->path($name);

        // Then
        expect($path)->toBeNull();
    })->with(['unknown file' => 'missing.log', 'path outside the logs' => '../.env', 'not a log' => '.gitignore']);

    it('resolves a listed log file to its full path', function (): void {
        // Given
        useTemporaryLogs(['laravel.log' => '']);

        // When
        $path = (new LogReader)->path('laravel.log');

        // Then
        expect($path)->toBe(storage_path('logs/laravel.log'));
    });
});

describe('entries', function (): void {
    it('reads entries newest first, each with the stack trace lines that follow it', function (): void {
        // Given
        $log = implode("\n", [
            logLine('info', 'Started', '2026-09-26 10:00:00'),
            logLine('error', 'Boom {"exception":"[object] (RuntimeException(code: 0): Boom)', '2026-09-26 11:00:00'),
            '[stacktrace]',
            '#0 /app/Http/Controller.php(12): run()',
            '"}',
        ])."\n";
        useTemporaryLogs(['laravel.log' => $log]);

        // When
        $page = (new LogReader)->entries(storage_path('logs/laravel.log'));

        // Then
        expect($page['before'])->toBeNull()
            ->and($page['entries'])->toBe([
                [
                    'level' => 'error',
                    'environment' => 'production',
                    'date' => '2026-09-26T11:00:00+00:00',
                    'message' => 'Boom {"exception":"[object] (RuntimeException(code: 0): Boom)',
                    'details' => "[stacktrace]\n#0 /app/Http/Controller.php(12): run()\n\"}",
                ],
                [
                    'level' => 'info',
                    'environment' => 'production',
                    'date' => '2026-09-26T10:00:00+00:00',
                    'message' => 'Started',
                    'details' => '',
                ],
            ]);
    });

    it('pages back through a large log without losing or repeating an entry', function (): void {
        // Given
        $messages = array_map(fn (int $number): string => sprintf('entry-%04d %s', $number, str_repeat('x', 200)), range(1, 1200));
        $log = implode("\n", array_map(fn (string $message): string => logLine('info', $message)."\n#0 trace line", $messages))."\n";
        useTemporaryLogs(['laravel.log' => $log]);
        $reader = new LogReader;

        // When
        $firstPage = $reader->entries(storage_path('logs/laravel.log'));
        $entries = allLogEntries(storage_path('logs/laravel.log'));

        // Then
        expect($firstPage['entries'])->toHaveCount(50)
            ->and($firstPage['before'])->toBeInt()
            ->and(array_column($entries, 'message'))->toBe(array_reverse($messages))
            ->and(array_unique(array_column($entries, 'details')))->toBe(['#0 trace line']);
    });

    it('keeps only entries at or above the requested level', function (): void {
        // Given
        $log = implode("\n", [
            logLine('debug', 'debug entry'),
            logLine('warning', 'warning entry'),
            logLine('error', 'error entry'),
            logLine('critical', 'critical entry'),
        ])."\n";
        useTemporaryLogs(['laravel.log' => $log]);

        // When
        $entries = allLogEntries(storage_path('logs/laravel.log'), minimumLevel: 'error');

        // Then
        expect(array_column($entries, 'message'))->toBe(['critical entry', 'error entry']);
    });

    it('keeps only entries whose message or details contain the search text, ignoring case', function (): void {
        // Given
        $log = implode("\n", [
            logLine('error', 'Payment failed'),
            logLine('error', 'Query failed'),
            '#0 PaymentGateway->charge()',
            logLine('info', 'User signed in'),
        ])."\n";
        useTemporaryLogs(['laravel.log' => $log]);

        // When
        $entries = allLogEntries(storage_path('logs/laravel.log'), search: 'payment');

        // Then
        expect(array_column($entries, 'message'))->toBe(['Query failed', 'Payment failed']);
    });

    it('reads a log that is not in Laravel\'s format one line at a time', function (): void {
        // Given
        useTemporaryLogs(['ssr.log' => "Starting SSR server on port 13714...\n\nInertia SSR server started.\n"]);

        // When
        $entries = allLogEntries(storage_path('logs/ssr.log'));

        // Then
        expect($entries)->toBe([
            ['level' => null, 'environment' => null, 'date' => null, 'message' => 'Inertia SSR server started.', 'details' => ''],
            ['level' => null, 'environment' => null, 'date' => null, 'message' => 'Starting SSR server on port 13714...', 'details' => ''],
        ]);
    });

    it('keeps lines written before the first entry header', function (): void {
        // Given
        useTemporaryLogs(['laravel.log' => "#12 {main}\n\"}\n".logLine('info', 'Started')."\n"]);

        // When
        $entries = allLogEntries(storage_path('logs/laravel.log'));

        // Then
        expect(array_column($entries, 'message'))->toBe(['Started', '#12 {main}'])
            ->and($entries[1]['details'])->toBe('"}');
    });

    it('returns nothing for an empty log', function (): void {
        // Given
        useTemporaryLogs(['laravel.log' => '']);

        // When
        $page = (new LogReader)->entries(storage_path('logs/laravel.log'));

        // Then
        expect($page)->toBe(['entries' => [], 'before' => null]);
    });
});

describe('clear', function (): void {
    it('empties the log but keeps the file', function (): void {
        // Given
        useTemporaryLogs(['laravel.log' => logLine('error', 'Boom')."\n"]);

        // When
        (new LogReader)->clear(storage_path('logs/laravel.log'));

        // Then
        expect(storage_path('logs/laravel.log'))->toBeFile()
            ->and(file_get_contents(storage_path('logs/laravel.log')))->toBe('');
    });
});
