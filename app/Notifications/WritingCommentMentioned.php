<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushChannel;

class WritingCommentMentioned extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Comment $comment, protected User $user)
    {
        $this->notification = [
            'title' => __('Updates from :name at :site', [
                'name' => $this->user->getName(),
                'site' => getSiteConfig('name'),
            ]),
            'greeting' => __('Hello!'),
            'body' => __('We knew it from the very beginning: you are such a magnetic person. :name just mentioned you in a comment at :site.', [
                'name' => $this->user->getName(),
                'site' => getSiteConfig('name'),
            ]),
            'footer' => __('Thank you for being part of the hood!'),
            'url' => route('writings.show', $this->comment->writing).'#comment-'.$this->comment->id,
            'action' => __('View comment'),
            'icon' => asset('images/logo-192.png'),
            'tag' => getSiteConfig('name'),
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast', WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'writing_id' => $this->comment->writing?->id,
            'user_id' => $this->user->id,
            'url' => $this->notification['url'],
        ];
    }
}
