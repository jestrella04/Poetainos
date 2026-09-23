<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class WritingCommentMentioned extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Comment $comment, protected User $user)
    {
        $this->content = $this->actorContent(
            $this->user,
            __('We knew it from the very beginning: you are such a magnetic person. :name just mentioned you in a comment at :site.', $this->actorPlaceholders($this->user)),
            route('writings.show', $this->comment->writing).'#comment-'.$this->comment->id,
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
        return [...$this->mailChannelIfWanted($notifiable), ...parent::via($notifiable)];
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
            'url' => $this->content['url'],
        ];
    }
}
