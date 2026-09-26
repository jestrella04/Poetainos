<?php

namespace App\Console\Commands;

use App\Services\AuraCalculator;
use Illuminate\Console\Command;

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
    public function handle(AuraCalculator $calculator): int
    {
        // Writings first: a user's aura counts the awards the writings' update may grant
        $calculator->updateAllWritingAura();
        $calculator->updateAllUserAura();

        return self::SUCCESS;
    }
}
