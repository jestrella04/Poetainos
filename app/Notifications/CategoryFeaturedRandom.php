<?php

namespace App\Notifications;

use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\FacebookPoster\FacebookPosterChannel;
use NotificationChannels\FacebookPoster\FacebookPosterPost;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;

class CategoryFeaturedRandom extends Notification
{
    use Queueable;

    protected string $msg;

    protected string $url;

    public function __construct(protected Category $category)
    {
        $this->msg = __('Discover all the beauty we have for you under the ":category" category.', [
            'category' => $this->category->name,
        ]);
        $this->url = $this->category->path();
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
        $msg = $this->msg.' '.$this->url;

        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster(mixed $notifiable): FacebookPosterPost
    {
        return (new FacebookPosterPost($this->msg))->withLink($this->url);
    }
}
