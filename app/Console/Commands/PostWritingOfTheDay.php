<?php

namespace App\Console\Commands;

use App\Models\DailySelection;
use App\Notifications\Channels\FacebookPageChannel;
use App\Notifications\WritingOfTheDayPosted;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

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
    protected $description = 'Post the writing that is the pick of the day on the Facebook Page';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->isFacebookConfigured() === false) {
            $this->warn('The Facebook Page is not configured, nothing was posted.');

            return self::SUCCESS;
        }

        $writing = DailySelection::pickForToday()->writing()->firstOrFail();

        Notification::route(FacebookPageChannel::class, config('services.facebook.page_id'))
            ->notify(new WritingOfTheDayPosted($writing));

        return self::SUCCESS;
    }

    private function isFacebookConfigured(): bool
    {
        return filled(config('services.facebook.page_id'))
            && filled(config('services.facebook.page_access_token'));
    }
}
