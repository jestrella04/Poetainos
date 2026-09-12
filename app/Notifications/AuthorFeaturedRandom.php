<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\FacebookPoster\FacebookPosterChannel;
use NotificationChannels\FacebookPoster\FacebookPosterPost;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;

class AuthorFeaturedRandom extends Notification
{
    use Queueable;

    protected string $msg;

    protected string $url;

    public function __construct(protected User $author)
    {
        $this->msg = __(':author is one of our most prominent authors.');
        $this->msg = $this->msg.' '.__('You are invited to discover all the magic present in their writings. #poetry');
        $this->url = $this->author->writingsPath();
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }

    public function toTwitter(mixed $notifiable): TwitterStatusUpdate
    {
        $msg = str_replace(':author', $this->author->getTwitterUsername(), $this->msg).' '.$this->url;

        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster(mixed $notifiable): FacebookPosterPost
    {
        $msg = str_replace(':author', $this->author->getName(), $this->msg);

        return (new FacebookPosterPost($msg))->withLink($this->url);
    }
}
