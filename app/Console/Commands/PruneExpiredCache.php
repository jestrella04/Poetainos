<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * The file cache only deletes an expired entry when its key is read again, so
 * short-lived keys that are never revisited (such as view cooldowns) pile up
 * on disk. This removes every entry whose expiration has already passed.
 */
class PruneExpiredCache extends Command
{
    /**
     * Every file cache entry starts with its expiration as a 10-digit UNIX timestamp.
     */
    private const EXPIRATION_LENGTH = 10;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:prune-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired entries from the file cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $directory = (string) config('cache.stores.file.path');

        if (File::isDirectory($directory) === false) {
            return self::SUCCESS;
        }

        $now = Carbon::now()->getTimestamp();
        $pruned = 0;

        foreach (File::allFiles($directory) as $file) {
            if ($this->isExpired($file->getPathname(), $now) === true) {
                File::delete($file->getPathname());
                $pruned++;
            }
        }

        $this->info(sprintf('Pruned %d expired cache entries.', $pruned));

        return self::SUCCESS;
    }

    /**
     * Files that don't start with a timestamp aren't cache entries and are left alone.
     */
    private function isExpired(string $path, int $now): bool
    {
        $expiration = file_get_contents($path, false, null, 0, self::EXPIRATION_LENGTH);

        return is_string($expiration) === true
            && strlen($expiration) === self::EXPIRATION_LENGTH
            && ctype_digit($expiration) === true
            && (int) $expiration <= $now;
    }
}
