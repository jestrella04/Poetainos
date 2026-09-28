<?php

namespace App\Notifications;

use App\Models\Writing;
use App\Notifications\Channels\FacebookPageChannel;
use App\Notifications\Channels\ThreadsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Announces the writing of the day on the site's Facebook Page and Threads
 * account. Not a PoetainosNotification: it is a public post, not a message
 * to a user.
 */
class WritingOfTheDayPosted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Threads counts each emoji as its UTF-8 byte length towards this limit.
     */
    private const THREADS_MAX_LENGTH = 500;

    public int $tries = 3;

    /**
     * Room for the Facebook post, or for the Threads container (created again
     * without the Instagram share when Threads rejects it), kept under the
     * queue's retry_after (90) so a running post is never picked up by a
     * second worker. PublishThreadsContainer publishes the container.
     */
    public int $timeout = 85;

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
     * Get the notification's delivery channels: the networks the notifiable
     * was routed to.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return array_values(array_filter(
            [FacebookPageChannel::class, ThreadsChannel::class],
            fn (string $channel): bool => $notifiable->routeNotificationFor($channel) !== null,
        ));
    }

    /**
     * The message and link of the Facebook Page post.
     *
     * @return array{message: string, link: string}
     */
    public function toFacebookPage(mixed $notifiable): array
    {
        return [
            'message' => $this->message($this->writing->excerpt()),
            'link' => $this->writing->path(),
        ];
    }

    /**
     * The Facebook Page message, with the excerpt shortened as needed to fit
     * in a Threads post, and the link shown as a preview.
     *
     * @return array{text: string, link_attachment: string}
     */
    public function toThreads(mixed $notifiable): array
    {
        $excerpt = $this->writing->excerpt();
        $overflow = $this->threadsLength($this->message($excerpt)) - self::THREADS_MAX_LENGTH;

        if ($overflow > 0) {
            // One more character for the ellipsis that a shortened excerpt ends with
            $excerpt = $this->writing->excerpt(max(0, mb_strlen($excerpt) - $overflow - 1));
        }

        return [
            'text' => $this->message($excerpt),
            'link_attachment' => $this->writing->path(),
        ];
    }

    private function message(string $excerpt): string
    {
        $site = (string) getSiteConfig('name');
        $paragraphs = [
            __('✨ Writing of the day ✨'),
            __('":title", by :author', [
                'title' => $this->writing->title,
                'author' => $this->writing->author?->getName() ?? '',
            ]),
            '“'.$excerpt.'”',
            __('Keep reading on :site and leave the author a few words 👇', ['site' => $site]),
            // A hashtag can't hold spaces
            __('#Poetry #WritingOfTheDay #:site', ['site' => (string) preg_replace('/\s+/', '', $site)]),
        ];

        return implode("\n\n", $paragraphs);
    }

    private function threadsLength(string $text): int
    {
        preg_match_all('/\p{Extended_Pictographic}\x{FE0F}?/u', $text, $emojis);

        return array_reduce(
            $emojis[0],
            fn (int $length, string $emoji): int => $length + strlen($emoji) - mb_strlen($emoji),
            mb_strlen($text),
        );
    }
}
