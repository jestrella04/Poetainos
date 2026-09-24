<?php

namespace App\Notifications\Channels;

use App\Notifications\WritingOfTheDayPosted;
use Illuminate\Support\Facades\Http;

/**
 * Publishes a notification as a post on the site's Facebook Page through the
 * Graph API. The notifiable routes it to the page id; the page access token
 * comes from config. A failed request throws, so the queued job is retried.
 */
class FacebookPageChannel
{
    private const TIMEOUT_SECONDS = 15;

    public function send(mixed $notifiable, WritingOfTheDayPosted $notification): void
    {
        $pageId = $notifiable->routeNotificationFor(self::class, $notification);

        Http::timeout(self::TIMEOUT_SECONDS)
            ->asForm()
            ->post(sprintf('https://graph.facebook.com/%s/%s/feed', config('services.facebook.graph_version'), $pageId), [
                ...$notification->toFacebookPage($notifiable),
                'access_token' => config('services.facebook.page_access_token'),
            ])
            ->throw();
    }
}
