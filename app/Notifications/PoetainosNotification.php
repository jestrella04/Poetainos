<?php

namespace App\Notifications;

use App\Events\NotificationEvent;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Base for notifications that build their content once, in the
 * constructor, into $this->content (title/greeting/body/footer/url/
 * action/icon/tag) and deliver it identically across mail, web push and
 * broadcast. Subclasses keep their own constructor, via(), and toArray().
 */
abstract class PoetainosNotification extends Notification
{
    /**
     * @var array<string, mixed>
     */
    protected array $content = [];

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
     * Get the notification's delivery channels: the in-app notification, live
     * broadcast and web push. Subclasses that also email add `mailChannelIfWanted()`.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
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
            ->subject($this->content['title'])
            ->greeting($this->content['greeting'])
            ->line($this->content['body'])
            ->action($this->content['action'], $this->content['url'])
            ->line($this->content['footer']);
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
            ->title($this->content['title'])
            ->icon($this->content['icon'])
            ->body($this->content['body'])
            ->action($this->content['action'], $this->content['url'])
            ->options(['TTL' => 1000])
            ->renotify()
            ->requireInteraction()
            ->tag($this->content['tag']);
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
