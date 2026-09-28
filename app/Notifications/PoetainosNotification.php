<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Base for notifications whose content() is delivered identically across
 * mail and web push, while the broadcast only refreshes the recipient's
 * unread badge. The content is built when the notification is sent, in the
 * queue worker, not when it is dispatched. Subclasses keep their own
 * constructor, via(), and toArray().
 */
abstract class PoetainosNotification extends Notification
{
    /**
     * What the notification says to the given recipient.
     */
    abstract protected function content(mixed $notifiable): NotificationContent;

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
     */
    protected function actorContent(User $actor, string $body, string $url, string $action): NotificationContent
    {
        return $this->siteContent(__('Updates from :name at :site', $this->actorPlaceholders($actor)), $body, $url, $action);
    }

    /**
     * The content of a notification from the site, with its greeting,
     * footer, icon and tag.
     */
    protected function siteContent(string $title, string $body, string $url, string $action): NotificationContent
    {
        return new NotificationContent(
            title: $title,
            greeting: __('Hello!'),
            body: $body,
            footer: __('Thank you for being part of the hood!'),
            url: $url,
            action: $action,
            icon: asset('images/logo-192.png'),
            tag: (string) getSiteConfig('name'),
        );
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
        $content = $this->content($notifiable);

        return (new MailMessage)
            ->subject($content->title)
            ->greeting($content->greeting)
            ->line($content->body)
            ->action($content->action, $content->url)
            ->line($content->footer);
    }

    /**
     * Get the web push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @param  mixed  $notification
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $content = $this->content($notifiable);

        return (new WebPushMessage)
            ->title($content->title)
            ->icon($content->icon)
            ->body($content->body)
            ->action($content->action, $content->url)
            ->options(['TTL' => 1000])
            ->renotify()
            ->requireInteraction()
            ->tag($content->tag);
    }

    /**
     * Get the broadcastable representation of the notification: the
     * recipient's unread count, for the badge. `via()` lists the database
     * channel first, so the count already includes this notification.
     *
     * @param  mixed  $notifiable
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'unread' => $notifiable instanceof User ? $notifiable->unreadNotifications()->count() : 0,
        ]);
    }
}
