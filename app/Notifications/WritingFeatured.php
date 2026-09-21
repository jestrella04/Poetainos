<?php

namespace App\Notifications;

use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\FacebookPoster\FacebookPosterChannel;
use NotificationChannels\FacebookPoster\FacebookPosterPost;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;
use NotificationChannels\WebPush\WebPushChannel;

class WritingFeatured extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Writing $writing)
    {
        $this->notification = [
            'title' => __('Your writing has been awarded with a Golden Flower'),
            'greeting' => __('Hello!'),
            'body' => __('Congratulations, your writing ":title" has been awarded with a Golden Flower at :site', [
                'title' => $this->writing->title,
                'site' => getSiteConfig('name'),
            ]),
            'body_social' => [
                __('":title" by :author has been awarded with a #GoldenFlower.', [
                    'title' => $this->writing->title,
                ]),
                __('You cannot miss this! #poetry'),
                $this->writing->path(),
            ],
            'footer' => __('Thank you for being part of the hood!'),
            'url' => $this->writing->path(),
            'action' => __('View writing'),
            'icon' => asset('images/logo-192.png'),
            'tag' => getSiteConfig('name'),
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [...$this->mailChannelIfWanted($notifiable), 'database', TwitterChannel::class, FacebookPosterChannel::class, WebPushChannel::class];
    }

    public function toTwitter(mixed $notifiable): TwitterStatusUpdate
    {
        $msg = implode(' ', $this->notification['body_social']);
        $msg = str_replace(':author', $this->writing->author?->twitterHandleOrName() ?? '', $msg);
        $msg = $msg.' '.$this->notification['url'];

        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster(mixed $notifiable): FacebookPosterPost
    {
        $msg = implode(' ', $this->notification['body_social']);
        $msg = str_replace(':author', $this->writing->author?->getName() ?? '', $msg);

        return (new FacebookPosterPost($msg))->withLink($this->notification['url']);
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
            'writing_id' => $this->writing->id,
        ];
    }
}
