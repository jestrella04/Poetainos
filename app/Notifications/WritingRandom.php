<?php

namespace App\Notifications;

use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\FacebookPoster\FacebookPosterChannel;
use NotificationChannels\FacebookPoster\FacebookPosterPost;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;

class WritingRandom extends Notification
{
    use Queueable;

    protected string $msg;

    protected string $url;

    public function __construct(protected Writing $writing)
    {
        $this->msg = __('":title" by :author is our #SelectionOfTheDay.', [
            'title' => $this->writing->title,
        ]);
        $this->msg = $this->msg.' '.__('Go read it, what are you waiting for? #poetry');
        $this->url = $this->writing->path();
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
        $msg = str_replace(':author', $this->writing->author?->twitterHandleOrName() ?? '', $this->msg).' '.$this->url;

        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster(mixed $notifiable): FacebookPosterPost
    {
        $msg = str_replace(':author', $this->writing->author?->getName() ?? '', $this->msg);

        return (new FacebookPosterPost($msg))->withLink($this->url);
    }
}
