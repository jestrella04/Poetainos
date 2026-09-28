<?php

namespace App\Notifications\Channels;

use App\Jobs\PublishThreadsContainer;
use App\Models\PublishingAccount;
use App\Notifications\WritingOfTheDayPosted;
use App\Services\ThreadsClient;

/**
 * Posts a notification on the site's Threads account. The notifiable routes it
 * to the account id; the token is read when sending, so a queued post uses a
 * token refreshed after it was queued. Threads builds the post's container
 * asynchronously, so this only creates it and a PublishThreadsContainer job
 * publishes it once it is finished.
 */
class ThreadsChannel
{
    public function __construct(private ThreadsClient $threads) {}

    public function send(mixed $notifiable, WritingOfTheDayPosted $notification): void
    {
        $userId = $notifiable->routeNotificationFor(self::class, $notification);
        $post = $notification->toThreads($notifiable);

        $container = $this->threads->createTextContainer(
            $userId,
            PublishingAccount::where('provider', PublishingAccount::THREADS)->firstOrFail()->access_token,
            $post['text'],
            $post['link_attachment'],
        );

        PublishThreadsContainer::dispatch($userId, $container['id'], $container['isSharedToInstagram'])
            ->delay(PublishThreadsContainer::SECONDS_BETWEEN_STATUS_CHECKS);
    }
}
