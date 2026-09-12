<?php

namespace App\Notifications;

use App\Events\NotificationEvent;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WritingShelved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Writing $writing, protected User $user) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
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
