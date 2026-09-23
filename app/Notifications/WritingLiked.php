<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class WritingLiked extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Writing $writing, protected User $user)
    {
        $this->content = $this->actorContent(
            $this->user,
            __('Isn\'t it amazing, :name likes your writing at :site.', $this->actorPlaceholders($this->user)),
            route('writings.show', $this->writing),
            __('View writing'),
        );
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
            'writing_id' => $this->writing->id,
            'user_id' => $this->user->id,
        ];
    }
}
