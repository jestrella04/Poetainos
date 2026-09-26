<?php

namespace App\Console\Commands;

use App\Services\ContentDeleter;
use App\Services\ExtraInfoCopyVerifier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * The last step of moving users.extra_info and writings.extra_info into
 * columns: once the copy is verified, archive the JSON, prune what past
 * deletions left behind, and drop the two JSON columns. Run by hand after
 * the copying migration has been deployed and the site checked.
 */
class FinalizeExtraInfo extends Command
{
    private const SAMPLE_SIZE = 20;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extra-info:finalize
        {--verify-only : Only report how the copy compares, changing nothing}
        {--copied-at= : When the copying migration ran, if not the earliest profile creation time}
        {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the extra_info copy, then archive and drop the legacy JSON columns';

    /**
     * Execute the console command.
     */
    public function handle(ExtraInfoCopyVerifier $verifier, ContentDeleter $deleter): int
    {
        if ($verifier->hasLegacyColumns() === false) {
            $this->info('The extra_info columns are already gone; nothing to do.');

            return self::SUCCESS;
        }

        $copiedAt = $this->option('copied-at') !== null ? Carbon::parse((string) $this->option('copied-at')) : $verifier->copiedAt();
        $differences = $verifier->differences($copiedAt);
        $editedCount = $differences->where('changedSinceCopy', true)->count();
        $lost = $differences->where('changedSinceCopy', false)->values()->all();

        $this->reportDifferences($copiedAt, $editedCount, $lost);

        if ($lost !== []) {
            $this->error(count($lost)." values weren't copied. Nothing was changed; re-run the copying migration's logic for them first.");

            return self::FAILURE;
        }

        if ($this->option('verify-only') === true) {
            $this->info('The copy is complete.');

            return self::SUCCESS;
        }

        if ($this->option('force') !== true && $this->confirm('Archive and drop users.extra_info and writings.extra_info?') === false) {
            $this->line('Nothing was changed.');

            return self::FAILURE;
        }

        $this->info('Archived the JSON to '.Storage::disk('local')->path($this->archive()).'.');

        $pruned = $deleter->deleteOrphans();
        $this->info("Pruned {$pruned['likes']} orphaned likes and {$pruned['notifications']} orphaned notifications.");

        $this->dropLegacyColumns();
        $this->info('Dropped users.extra_info and writings.extra_info.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{table: string, id: int, field: string, legacy: mixed, current: mixed, changedSinceCopy: bool}>  $lost
     */
    private function reportDifferences(?Carbon $copiedAt, int $editedCount, array $lost): void
    {
        $this->line('Copy made at: '.($copiedAt?->toDateTimeString() ?? 'unknown (every difference counts as not copied)'));
        $this->line("Values users changed since then: {$editedCount}");
        $this->line('Values not copied: '.count($lost));

        if ($lost !== []) {
            $this->table(
                ['Table', 'Id', 'Field', 'In extra_info', 'Now'],
                collect($lost)->take(self::SAMPLE_SIZE)->map(fn (array $difference): array => [
                    $difference['table'],
                    $difference['id'],
                    $difference['field'],
                    json_encode($difference['legacy']),
                    json_encode($difference['current']),
                ])->all(),
            );
        }
    }

    /**
     * Save every row's extra_info to a JSON file, returning its path on the local disk.
     */
    private function archive(): string
    {
        $path = 'backups/extra-info-'.Carbon::now()->format('Ymd-His').'.json';
        $archive = [];

        foreach (['users', 'writings'] as $table) {
            if (Schema::hasColumn($table, 'extra_info') === true) {
                $archive[$table] = DB::table($table)
                    ->whereNotNull('extra_info')
                    ->pluck('extra_info', 'id')
                    ->map(fn (string $json): mixed => json_decode($json, true))
                    ->all();
            }
        }

        Storage::disk('local')->put($path, (string) json_encode($archive, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    private function dropLegacyColumns(): void
    {
        foreach (['users', 'writings'] as $table) {
            if (Schema::hasColumn($table, 'extra_info') === true) {
                Schema::table($table, fn ($blueprint) => $blueprint->dropColumn('extra_info'));
            }
        }
    }
}
