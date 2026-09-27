<?php

namespace App\Console\Commands;

use App\Models\PublishingAccount;
use App\Services\ThreadsClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ConnectThreads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'threads:connect
                            {--short-lived : The token lasts one hour and must be exchanged for a long-lived one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store the access token of the Threads account the site posts to';

    /**
     * Execute the console command.
     */
    public function handle(ThreadsClient $threads): int
    {
        if ($this->option('short-lived') === true && blank(config('services.threads.app_secret'))) {
            $this->error('THREADS_APP_SECRET is required to exchange a short-lived token.');

            return self::FAILURE;
        }

        $accessToken = (string) $this->secret('Threads access token');
        $expiresAt = null;

        if ($this->option('short-lived') === true) {
            $longLived = $threads->exchangeForLongLived($accessToken);
            $accessToken = $longLived['access_token'];
            $expiresAt = Carbon::now()->addSeconds($longLived['expires_in']);
        }

        $account = PublishingAccount::firstOrNew(['provider' => PublishingAccount::THREADS]);
        $account->fill([
            'account_id' => $threads->fetchUserId($accessToken),
            'access_token' => $accessToken,
            'expires_at' => $expiresAt,
        ])->save();

        $this->info("Connected the Threads account {$account->account_id}.");

        return self::SUCCESS;
    }
}
