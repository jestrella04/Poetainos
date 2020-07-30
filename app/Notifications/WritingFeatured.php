<?php

namespace App\Notifications;

use App\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;

class WritingFeatured extends Notification implements ShouldQueue
{
    use Queueable;

    protected $writing;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Writing $writing)
    {
        $this->writing = $writing;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', TwitterChannel::class];
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
            ->subject(__('Your writing has been featured on the home page'))
            ->greeting(__('Hello!'))
            ->line(__('Your writing has been featured on the home page'))
            ->action(__('View writing'), route('writings.show', $this->writing))
            ->line(__('Thank you for being part of the hood!'));
    }

    public function toTwitter($notifiable)
    {
        $msg = __('":title" by :author has just been featured on our home page.', [
            'title' => $this->writing->title,
            'author' => $this->writing->author->getTwitterUsername()
        ]);

        $msg = $msg . ' ' . __('Go read it, what are you waiting for? #poetry');
        $msg = $msg . ' ' . $this->writing->path();

        return new TwitterStatusUpdate($msg);
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
