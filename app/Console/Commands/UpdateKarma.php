<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateKarma extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karma:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate the karma of every user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        User::query()->chunkById(200, function (Collection $users): void {
            $users->each->updateKarma();
        });

        return self::SUCCESS;
    }
}
