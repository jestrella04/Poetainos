<?php

namespace App\Console\Commands;

use App\Models\DailySelection;
use App\Notifications\WritingRandom;
use Illuminate\Console\Command;

class PostRandomWriting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'writing:random';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post the writing that is the pick of the day';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $writing = DailySelection::pickForToday()->writing()->firstOrFail();
        $writing->author?->notify(new WritingRandom($writing));

        return self::SUCCESS;
    }
}
