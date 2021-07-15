<?php

namespace App\Notifications;

use App\User;
use App\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class WritingCommented extends Notification implements ShouldQueue
{
    use Queueable;

    protected $writing;
    protected $user;
    protected $notification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Writing $writing, User $user)
    {
        $this->writing = $writing;
        $this->user = $user;
        $this->notification = [
            'title' => __(':name commented on your writing', ['name' => $this->user->getName()]),
            'greeting' => __('Hello!'),
            'body' => __(':name commented on your writing', ['name' => $this->user->getName()]),
            'footer' => __('Thank you for being part of the hood!'),
            'url' => route('writings.show', $this->writing),
            'action' => __('View writing'),
            'icon' => mix('/static/images/logo.svg'),
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
        return ['mail', 'database', WebPushChannel::class];
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
            ->options(['TTL' => 1000]);
            // ->data(['id' => $notification->id])
            // ->badge()
            // ->dir()
            // ->image()
            // ->lang()
            // ->renotify()
            // ->requireInteraction()
            // ->tag()
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
            'user_id' => $this->user->id,
        ];
    }
}
