<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushChannel;

class CommentLiked extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Comment $comment, protected User $user)
    {
        $this->notification = $this->actorContent(
            $this->user,
            __('Isn\'t it amazing, :name likes your comment at :site.', $this->actorPlaceholders($this->user)),
            $this->comment->writing?->path() ?? url('/'),
            __('View comment'),
        );
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
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
            'comment_id' => $this->comment->id,
            'user_id' => $this->user->id,
        ];
    }
}
