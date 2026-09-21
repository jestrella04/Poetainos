<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\FacebookPoster\FacebookPosterChannel;
use NotificationChannels\FacebookPoster\FacebookPosterPost;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;

/**
 * Base for notifications that only post a message and a link on the site's
 * social networks. The message may contain an `:author` placeholder, filled
 * in per network: a Twitter handle on X, the display name on Facebook.
 */
abstract class SocialPostNotification extends Notification
{
    use Queueable;

    protected string $message;

    protected string $url;

    /**
     * The user the `:author` placeholder of the message stands for, if it has one.
     */
    protected function socialAuthor(): ?User
    {
        return null;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [TwitterChannel::class, FacebookPosterChannel::class];
    }

    public function toTwitter(mixed $notifiable): TwitterStatusUpdate
    {
        return new TwitterStatusUpdate($this->post($this->socialAuthor()?->twitterHandleOrName()).' '.$this->url);
    }

    public function toFacebookPoster(mixed $notifiable): FacebookPosterPost
    {
        return (new FacebookPosterPost($this->post($this->socialAuthor()?->getName())))->withLink($this->url);
    }

    private function post(?string $author): string
    {
        return str_replace(':author', $author ?? '', $this->message);
    }
}
