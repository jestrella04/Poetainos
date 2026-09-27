<?php

namespace App\Console\Commands;

use App\Models\PublishingAccount;
use App\Services\ThreadsClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;

class RefreshThreadsToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'threads:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew the access token of the Threads account for another 60 days';

    /**
     * Execute the console command.
     */
    public function handle(ThreadsClient $threads): int
    {
        $account = PublishingAccount::threads();

        if ($account === null) {
            $this->warn('No Threads account is connected, nothing was refreshed.');

            return self::SUCCESS;
        }

        // Threads refuses to refresh a token that is less than a day old
        if ($account->updated_at?->gt(Carbon::now()->subDay()) === true) {
            $this->info('The Threads token is less than a day old, it was not refreshed.');

            return self::SUCCESS;
        }

        try {
            $refreshed = $threads->refresh($account->access_token);
        } catch (RequestException $exception) {
            logger()->error('The Threads access token could not be refreshed.', ['error' => $exception->getMessage()]);
            $this->error('The Threads access token could not be refreshed. Run threads:connect with a new token before it expires.');

            return self::FAILURE;
        }

        $account->fill([
            'access_token' => $refreshed['access_token'],
            'expires_at' => Carbon::now()->addSeconds($refreshed['expires_in']),
        ])->save();

        $this->info("The Threads token now expires on {$account->expires_at?->toDateString()}.");

        return self::SUCCESS;
    }
}
