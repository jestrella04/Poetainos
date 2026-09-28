<?php

namespace App\Console\Commands;

use App\Services\ContentDeleter;
use Illuminate\Console\Command;

/**
 * Deletions used to leave likes and notifications pointing at writings,
 * comments and users that no longer exist. ContentDeleter now removes them
 * along with the content; this cleans up what earlier deletions left behind.
 */
class PruneOrphanedContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:prune-orphans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete likes, complaints and notifications that refer to deleted content';

    /**
     * Execute the console command.
     */
    public function handle(ContentDeleter $deleter): int
    {
        $pruned = $deleter->deleteOrphans();

        $this->info(sprintf('Pruned %d orphaned likes, %d orphaned complaints and %d orphaned notifications.', $pruned['likes'], $pruned['complaints'], $pruned['notifications']));

        return self::SUCCESS;
    }
}
