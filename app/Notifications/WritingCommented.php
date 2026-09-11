<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushChannel;

class WritingCommented extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    protected $writing;

    protected $user;

    protected $notification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Writing $writing, User $user)
    {
        $this->writing = $writing;
        $this->user = $user;
        $this->notification = [
            'title' => __('Updates from :name at :site', [
                'name' => $this->user->getName(),
                'site' => getSiteConfig('name'),
            ]),
            'greeting' => __('Hello!'),
            'body' => __('We love sharing the good news with you, :name just commented on your writing at :site.', [
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
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast', WebPushChannel::class];
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
            'writing_id' => $this->writing->id,
            'user_id' => $this->user->id,
        ];
    }
}
