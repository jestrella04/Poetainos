<?php

namespace App\Notifications;

use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushChannel;

class WritingFeatured extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Writing $writing)
    {
        $this->content = [
            'title' => __('Your writing has been awarded with a Golden Flower'),
            'greeting' => __('Hello!'),
            'body' => __('Congratulations, your writing ":title" has been awarded with a Golden Flower at :site', [
                'title' => $this->writing->title,
                'site' => getSiteConfig('name'),
            ]),
            'footer' => __('Thank you for being part of the hood!'),
            'url' => $this->writing->path(),
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
        return [...$this->mailChannelIfWanted($notifiable), 'database', WebPushChannel::class];
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
        ];
    }
}
