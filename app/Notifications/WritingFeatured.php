<?php

namespace App\Notifications;

use App\Models\Writing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class WritingFeatured extends PoetainosNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Writing $writing) {}

    protected function content(mixed $notifiable): NotificationContent
    {
        return $this->siteContent(
            __('Your writing has been awarded with a Golden Flower'),
            __('Congratulations, your writing ":title" has been awarded with a Golden Flower at :site', [
                'title' => $this->writing->title,
                'site' => getSiteConfig('name'),
            ]),
            $this->writing->path(),
            __('View writing'),
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
            'writing_id' => $this->writing->id,
        ];
    }
}
