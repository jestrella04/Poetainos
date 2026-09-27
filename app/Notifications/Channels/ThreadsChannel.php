<?php

namespace App\Notifications\Channels;

use App\Models\PublishingAccount;
use App\Notifications\WritingOfTheDayPosted;
use App\Services\ThreadsClient;

/**
 * Publishes a notification as a post on the site's Threads account. The
 * notifiable routes it to the account id; the token is read when sending, so
 * a queued post uses a token refreshed after it was queued.
 */
class ThreadsChannel
{
    public function __construct(private ThreadsClient $threads) {}

    public function send(mixed $notifiable, WritingOfTheDayPosted $notification): void
    {
        $userId = $notifiable->routeNotificationFor(self::class, $notification);
        $post = $notification->toThreads($notifiable);

        $this->threads->publishText(
            $userId,
            PublishingAccount::where('provider', PublishingAccount::THREADS)->firstOrFail()->access_token,
            $post['text'],
            $post['link_attachment'],
        );
    }
}
