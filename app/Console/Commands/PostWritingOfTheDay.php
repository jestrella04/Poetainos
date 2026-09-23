<?php

namespace App\Console\Commands;

use App\Models\DailySelection;
use App\Notifications\WritingOfTheDayPosted;
use Illuminate\Console\Command;

class PostWritingOfTheDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'writing:post-of-the-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post the writing that is the pick of the day on social media';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $writing = DailySelection::pickForToday()->writing()->firstOrFail();
        $writing->author?->notify(new WritingOfTheDayPosted($writing));

        return self::SUCCESS;
    }
}
