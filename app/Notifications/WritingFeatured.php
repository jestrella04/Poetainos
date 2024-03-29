<?php

namespace App\Notifications;

use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\FacebookPoster\FacebookPosterChannel;
use NotificationChannels\FacebookPoster\FacebookPosterPost;

class WritingFeatured extends Notification implements ShouldQueue
{
    use Queueable;

    protected $writing;
    protected $notification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Writing $writing)
    {
        $this->writing = $writing;
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
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', TwitterChannel::class, FacebookPosterChannel::class, WebPushChannel::class];
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
            ->subject($this->notification['title'])
            ->greeting($this->notification['greeting'])
            ->line($this->notification['body'])
            ->action($this->notification['action'], $this->notification['url'])
            ->line($this->notification['footer']);
    }

    public function toTwitter($notifiable)
    {
        $msg = implode(' ', $this->notification['body_social']);
        $msg = str_replace(':author', $this->writing->author->getTwitterUsername(), $msg);
        $msg = $msg . ' ' . $this->notification['url'];

        return new TwitterStatusUpdate($msg);
    }

    public function toFacebookPoster($notifiable)
    {
        $msg = implode(' ', $this->notification['body_social']);
        $msg = str_replace(':author', $this->writing->author->getName(), $msg);

        return (new FacebookPosterPost($msg))->withLink($this->notification['url']);
    }

    /**
     * Get the web push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @param  mixed  $notification
     * @return \Illuminate\Notifications\Messages\DatabaseMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->notification['title'])
            ->icon($this->notification['icon'])
            ->body($this->notification['body'])
            ->action($this->notification['action'], $this->notification['url'])
            ->options(['TTL' => 1000])
            ->renotify()
            ->requireInteraction()
            ->tag($this->notification['tag']);
        // ->data(['id' => $notification->id])
        // ->badge()
        // ->dir()
        // ->image()
        // ->lang()
        // ->vibrate()
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
            'writing_id' => $this->writing->id,
        ];
    }
}
