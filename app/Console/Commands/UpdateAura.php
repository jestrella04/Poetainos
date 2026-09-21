<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateAura extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aura:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate the aura of every writing and user, which changes as they get viewed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Writing::query()->chunkById(200, function (Collection $writings): void {
            $writings->each->updateAura();
        });

        User::query()->chunkById(200, function (Collection $users): void {
            $users->each->updateAura();
        });

        return self::SUCCESS;
    }
}
