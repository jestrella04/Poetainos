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

    protected $author;

    protected $msg;

    protected $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $author)
    {
        $this->author = $author;
        $this->msg = __(':author is one of our most prominent authors.');
        $this->msg = $this->msg.' '.__('You are invited to discover all the magic present in their writings. #poetry');
        $this->url = $this->author->writingsPath();
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
        $msg = str_replace(':author', $this->author->getTwitterUsername(), $this->msg).' '.$this->url;

        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster($notifiable)
    {
        $msg = str_replace(':author', $this->author->getName(), $this->msg);

        return (new FacebookPosterPost($msg))->withLink($this->url);
    }
}
