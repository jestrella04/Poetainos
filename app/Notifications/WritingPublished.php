<?php

namespace App\Notifications;

use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;
use NotificationChannels\FacebookPoster\FacebookPosterChannel;
use NotificationChannels\FacebookPoster\FacebookPosterPost;

class WritingPublished extends Notification implements ShouldQueue
{
    use Queueable;

    protected $writing;
    protected $msg;
    protected $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Writing $writing)
    {
        $this->writing = $writing;
        $this->msg = __('":title" by :author has just been published on our site.', [
            'title' => $this->writing->title,
        ]);
        $this->msg = $this->msg . ' ' . __('Go read it, what are you waiting for? #poetry');
        $this->url = $this->writing->path();
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
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toTwitter($notifiable)
    {
        $msg = str_replace(':author', $this->writing->author->getTwitterUsername(), $this->msg) . ' ' . $this->url;
        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster($notifiable) {
        $msg = str_replace(':author', $this->writing->author->getName(), $this->msg);
        return (new FacebookPosterPost($msg))->withLink($this->url);
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
}
