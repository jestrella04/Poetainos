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

    protected $category;

    protected $msg;

    protected $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
        $this->msg = __('Discover all the beauty we have for you under the ":category" category.', [
            'category' => $this->category->name,
        ]);
        $this->url = $this->category->path();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TwitterChannel::class, FacebookPosterChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toTwitter($notifiable)
    {
        $msg = $this->msg.' '.$this->url;

        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster($notifiable)
    {
        return (new FacebookPosterPost($this->msg))->withLink($this->url);
    }
}
