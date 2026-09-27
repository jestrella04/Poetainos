<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuraCalculator;
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
    public function handle(AuraCalculator $calculator): int
    {
        User::query()->chunkById(200, function (Collection $users) use ($calculator): void {
            $users->each(fn (User $user) => $calculator->updateUserKarma($user));
        });

        return self::SUCCESS;
    }
}
