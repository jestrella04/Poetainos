<?php

namespace App\Notifications;

use App\Events\NotificationEvent;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Base for notifications that build their content once, in the
 * constructor, into $this->notification (title/greeting/body/footer/url/
 * action/icon/tag) and deliver it identically across mail, web push and
 * broadcast. Subclasses keep their own constructor, via(), and toArray().
 */
abstract class PoetainosNotification extends Notification
{
    /**
     * @var array<string, mixed>
     */
    protected array $notification = [];

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
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
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
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
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<int|string, mixed>|null
     */
    public function toBroadcast($notifiable): ?array
    {
        return event(new NotificationEvent($notifiable));
    }
}
