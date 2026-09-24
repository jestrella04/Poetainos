<?php

namespace App\Notifications;

use App\Models\Writing;
use App\Notifications\Channels\FacebookPageChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Announces the writing of the day on the site's Facebook Page. Not a
 * PoetainosNotification: it is a public post, not a message to a user.
 */
class WritingOfTheDayPosted extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(protected Writing $writing) {}

    /**
     * Seconds to wait before each retry of a failed post.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [FacebookPageChannel::class];
    }

    /**
     * The message and link of the Facebook Page post.
     *
     * @return array{message: string, link: string}
     */
    public function toFacebookPage(mixed $notifiable): array
    {
        $paragraphs = [
            __('✨ Writing of the day ✨'),
            __('":title", by :author', [
                'title' => $this->writing->title,
                'author' => $this->writing->author?->getName() ?? '',
            ]),
            '“'.$this->writing->excerpt().'”',
            __('Keep reading on Poetainos and leave the author a few words 👇'),
            __('#Poetry #WritingOfTheDay #Poetainos'),
        ];

        return [
            'message' => implode("\n\n", $paragraphs),
            'link' => $this->writing->path(),
        ];
    }
}
