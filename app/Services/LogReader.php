<?php

namespace App\Services;

use Carbon\Carbon;
use Generator;

/**
 * Read access to the log files in storage/logs, so admins can follow them
 * from the admin panel instead of the server's shell. Entries are read
 * newest first, from the end of the file backwards, a page at a time,
 * so even a very large log only costs the bytes of the page being shown.
 */
class LogReader
{
    /**
     * Monolog's levels, least severe first.
     */
    public const LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    /**
     * Logs whose files the admin panel may not clear, so the audit trail of
     * what admins did outlives any admin who would rather erase it.
     */
    private const PROTECTED_PREFIX = 'security';

    private const ENTRIES_PER_PAGE = 50;

    private const CHUNK_BYTES = 64 * 1024;

    /**
     * How much of the file one page may scan, so a filter that matches
     * nothing can't make a request read a huge log in full.
     */
    private const SCAN_BUDGET_BYTES = 2 * 1024 * 1024;

    /**
     * Laravel's line format: "[2026-09-26 22:44:27] production.ERROR: message".
     */
    private const ENTRY_HEADER = '/^\[(\d{4}-\d{2}-\d{2}[T ][^\]]+)\] ([^\s.]+)\.([A-Z]+): (.*)$/';

    /**
     * Any entry header within the sampled start of a file marks it as a Monolog log.
     */
    private const ENTRY_HEADER_ANYWHERE = '/^\[\d{4}-\d{2}-\d{2}[T ][^\]]+\] [^\s.]+\.[A-Z]+: /m';

    private const FORMAT_SAMPLE_BYTES = 8 * 1024;

    /**
     * @return list<array{name: string, size: int, modified_at: Carbon, clearable: bool}>
     */
    public function files(): array
    {
        $paths = glob(storage_path('logs').'/*.log');
        $files = array_map(fn (string $path): array => [
            'name' => basename($path),
            'size' => (int) filesize($path),
            'modified_at' => Carbon::createFromTimestamp((int) filemtime($path)),
            'clearable' => $this->isClearable(basename($path)),
        ], $paths === false ? [] : array_values(array_filter($paths, is_file(...))));

        usort($files, fn (array $first, array $second): int => strcmp($first['name'], $second['name']));

        return $files;
    }

    /**
     * The full path of the named log, or null for any name that isn't one of
     * the listed files, which keeps requests from reaching outside storage/logs.
     */
    public function path(string $name): ?string
    {
        foreach ($this->files() as $file) {
            if ($file['name'] === $name) {
                return storage_path('logs/'.$name);
            }
        }

        return null;
    }

    /**
     * One page of entries, newest first, that end before the byte offset
     * `$before` (the end of the file when null). Only entries at or above
     * `$minimumLevel` and containing `$search` are returned. The page's own
     * `before` is where the next, older page starts, or null at the start of the file.
     *
     * @return array{entries: list<array{level: string|null, environment: string|null, date: string|null, message: string, details: string}>, before: int|null}
     */
    public function entries(string $path, ?int $before = null, ?string $minimumLevel = null, ?string $search = null): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return ['entries' => [], 'before' => null];
        }

        $isStructured = preg_match(self::ENTRY_HEADER_ANYWHERE, (string) fread($handle, self::FORMAT_SAMPLE_BYTES)) === 1;
        $startOffset = min($before ?? PHP_INT_MAX, (int) filesize($path));
        $oldestConsumedOffset = $startOffset;
        // Collected newest first while walking back to the header they belong to
        $continuationLines = [];
        $entries = [];
        $lines = $this->linesBackwards($handle, $startOffset);

        foreach ($lines as $offset => $line) {
            $hasMadeProgress = $oldestConsumedOffset < $startOffset;

            if (count($entries) === self::ENTRIES_PER_PAGE || ($startOffset - $offset >= self::SCAN_BUDGET_BYTES && $hasMadeProgress === true)) {
                break;
            }

            if ($isStructured === true && preg_match(self::ENTRY_HEADER, $line) !== 1) {
                $continuationLines[] = $line;

                continue;
            }

            $entry = $this->entry($line, array_reverse($continuationLines));

            if ((trim($line) !== '' || $continuationLines !== []) && $this->matches($entry, $minimumLevel, $search) === true) {
                $entries[] = $entry;
            }

            $continuationLines = [];
            $oldestConsumedOffset = $offset;
        }

        $hasReachedStart = $lines->valid() === false;
        fclose($handle);

        // Lines before the file's first entry header (e.g. left by a truncated write)
        if ($hasReachedStart === true && trim(implode('', $continuationLines)) !== '') {
            $orphanLines = array_reverse($continuationLines);
            $entry = $this->entry((string) array_shift($orphanLines), $orphanLines);

            if ($this->matches($entry, $minimumLevel, $search) === true) {
                $entries[] = $entry;
            }
        }

        return [
            'entries' => $entries,
            'before' => $hasReachedStart === true ? null : $oldestConsumedOffset,
        ];
    }

    /**
     * Whether the admin panel may empty the named log.
     */
    public function isClearable(string $name): bool
    {
        return str_starts_with($name, self::PROTECTED_PREFIX) === false;
    }

    /**
     * Empty the log, keeping the file (and its ownership) for the next write.
     */
    public function clear(string $path): void
    {
        file_put_contents($path, '');
    }

    /**
     * The lines that end before `$end`, newest first, keyed by the byte
     * offset each one starts at.
     *
     * @param  resource  $handle
     * @return Generator<int, string>
     */
    private function linesBackwards($handle, int $end): Generator
    {
        $cursor = $end;
        $carry = '';

        while ($cursor > 0) {
            $chunkLength = min($cursor, self::CHUNK_BYTES);
            $chunkStart = $cursor - $chunkLength;
            fseek($handle, $chunkStart);
            $lines = explode("\n", ((string) fread($handle, $chunkLength)).$carry);
            $cursor = $chunkStart;

            // The chunk may start partway through a line; finish it with the next, older chunk
            $carry = $chunkStart > 0 ? (string) array_shift($lines) : '';
            $offset = $chunkStart > 0 ? $chunkStart + strlen($carry) + 1 : 0;

            $offsets = [];
            foreach ($lines as $line) {
                $offsets[] = $offset;
                $offset += strlen($line) + 1;
            }

            for ($index = count($lines) - 1; $index >= 0; $index--) {
                yield $offsets[$index] => $lines[$index];
            }
        }
    }

    /**
     * @param  list<string>  $continuationLines
     * @return array{level: string|null, environment: string|null, date: string|null, message: string, details: string}
     */
    private function entry(string $firstLine, array $continuationLines): array
    {
        $details = rtrim(implode("\n", $continuationLines));

        if (preg_match(self::ENTRY_HEADER, $firstLine, $header) !== 1) {
            return ['level' => null, 'environment' => null, 'date' => null, 'message' => $firstLine, 'details' => $details];
        }

        return [
            'level' => strtolower($header[3]),
            'environment' => $header[2],
            'date' => Carbon::parse($header[1], config('app.timezone'))->toIso8601String(),
            'message' => $header[4],
            'details' => $details,
        ];
    }

    /**
     * @param  array{level: string|null, environment: string|null, date: string|null, message: string, details: string}  $entry
     */
    private function matches(array $entry, ?string $minimumLevel, ?string $search): bool
    {
        if ($minimumLevel !== null) {
            $severity = array_search($entry['level'], self::LEVELS, true);

            if ($severity === false || $severity < array_search($minimumLevel, self::LEVELS, true)) {
                return false;
            }
        }

        if ($search === null || $search === '') {
            return true;
        }

        return stripos($entry['message'], $search) !== false || stripos($entry['details'], $search) !== false;
    }
}
