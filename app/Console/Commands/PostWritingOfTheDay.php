<?php

namespace App\Console\Commands;

use App\Models\DailySelection;
use App\Models\PublishingAccount;
use App\Notifications\Channels\FacebookPageChannel;
use App\Notifications\Channels\ThreadsChannel;
use App\Notifications\WritingOfTheDayPosted;
use Illuminate\Console\Command;
use Illuminate\Notifications\AnonymousNotifiable;

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
    protected $description = 'Post the writing that is the pick of the day on the Facebook Page and Threads';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $networks = $this->configuredNetworks();

        if ($networks->routes === []) {
            $this->warn('No social network is configured, nothing was posted.');

            return self::SUCCESS;
        }

        $writing = DailySelection::pickForToday()->writing()->firstOrFail();

        $networks->notify(new WritingOfTheDayPosted($writing));

        return self::SUCCESS;
    }

    /**
     * An on-demand notifiable routed to each network the site can post to.
     */
    private function configuredNetworks(): AnonymousNotifiable
    {
        $networks = new AnonymousNotifiable;

        if ($this->isFacebookConfigured() === true) {
            $networks->route(FacebookPageChannel::class, config('services.facebook.page_id'));
        }

        $threadsAccount = PublishingAccount::threads();

        if ($threadsAccount !== null) {
            $networks->route(ThreadsChannel::class, $threadsAccount->account_id);
        }

        return $networks;
    }

    private function isFacebookConfigured(): bool
    {
        return filled(config('services.facebook.page_id'))
            && filled(config('services.facebook.page_access_token'));
    }
}
