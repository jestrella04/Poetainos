<?php

namespace App\Notifications;

use App\Comment;
use App\User;
use App\Events\NotificationEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class WritingCommentMentioned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;
    protected $user;
    protected $notification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Comment $comment, User $user)
    {
        $this->comment = $comment;
        $this->user = $user;
        $this->notification = [
            'title' => __('Updates from :name at :site', [
                'name' => $this->user->getName(),
                'site' => getSiteConfig('name')
            ]),
            'greeting' => __('Hello!'),
            'body' => __('We knew it from the very beginning: you are such a magnetic person. :name just mentioned you in a comment at :site.', [
                'name' => $this->user->getName(),
                'site' => getSiteConfig('name')
            ]),
            'footer' => __('Thank you for being part of the hood!'),
            'url' => route('writings.show', $this->comment->writing) . '#comment-' . $this->comment->id,
            'action' => __('View comment'),
            'icon' => asset('/static/images/logo-192.png'),
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
        return ['mail', 'database', 'broadcast', WebPushChannel::class];
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
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return event(new NotificationEvent($notifiable));
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
            'writing_id' => $this->comment->writing->id,
            'user_id' => $this->user->id,
            'url' => $this->notification['url']
        ];
    }
}
