<?php

namespace App\Console\Commands;

use App\Models\DailySelection;
use Illuminate\Console\Command;

class PickWritingOfTheDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'writing:pick-of-the-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the writing that is the pick of the day';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DailySelection::pickForToday();

        return self::SUCCESS;
    }
}
