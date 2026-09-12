<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushChannel;

class WritingLiked extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Writing $writing, protected User $user)
    {
        $this->notification = [
            'title' => __('Updates from :name at :site', [
                'name' => $this->user->getName(),
                'site' => getSiteConfig('name'),
            ]),
            'greeting' => __('Hello!'),
            'body' => __('Isn\'t it amazing?, :name likes your writing at :site.', [
                'name' => $this->user->getName(),
                'site' => getSiteConfig('name'),
            ]),
            'footer' => __('Thank you for being part of the hood!'),
            'url' => route('writings.show', $this->writing),
            'action' => __('View writing'),
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
            'writing_id' => $this->writing->id,
            'user_id' => $this->user->id,
        ];
    }
}
