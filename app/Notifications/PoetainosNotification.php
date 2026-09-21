<?php

namespace App\Notifications;

use App\Events\NotificationEvent;
use App\Models\User;
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
     * The values every "someone did something on your content" message interpolates.
     *
     * @return array{name: string, site: mixed}
     */
    protected function actorPlaceholders(User $actor): array
    {
        return ['name' => $actor->getName(), 'site' => getSiteConfig('name')];
    }

    /**
     * The content of a "someone did something on your content" notification;
     * only the body, the link and the action label differ between them.
     *
     * @return array<string, mixed>
     */
    protected function actorContent(User $actor, string $body, string $url, string $action): array
    {
        return [
            'title' => __('Updates from :name at :site', $this->actorPlaceholders($actor)),
            'greeting' => __('Hello!'),
            'body' => $body,
            'footer' => __('Thank you for being part of the hood!'),
            'url' => $url,
            'action' => $action,
            'icon' => asset('images/logo-192.png'),
            'tag' => getSiteConfig('name'),
        ];
    }

    /**
     * The mail channel, unless the recipient opted out of notification emails.
     *
     * @return array<int, string>
     */
    protected function mailChannelIfWanted(mixed $notifiable): array
    {
        return $notifiable instanceof User && $notifiable->wantsEmailNotifications() === true ? ['mail'] : [];
    }

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
