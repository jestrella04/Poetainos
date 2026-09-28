<?php

namespace App\Jobs;

use App\Models\PublishingAccount;
use App\Services\ThreadsClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

/**
 * Publishes a Threads container once Threads has finished building it. Every
 * run checks the container first, so running again after a timeout or a
 * killed worker never posts twice: a published container is left alone, one
 * still being built is checked again a few seconds later.
 */
class PublishThreadsContainer implements ShouldQueue
{
    use Queueable;

    public const SECONDS_BETWEEN_STATUS_CHECKS = 5;

    /**
     * Status checks while the container is built, plus a few publish retries.
     */
    public int $tries = 12;

    /**
     * A status check and a slow publish (up to 60 seconds), kept under the
     * queue's retry_after (90) so a running job is never picked up by a second worker.
     */
    public int $timeout = 85;

    public function __construct(
        public string $userId,
        public string $containerId,
        public bool $isSharedToInstagram,
    ) {}

    /**
     * Seconds to wait before retrying a failed publish.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 60, 300];
    }

    public function handle(ThreadsClient $threads): void
    {
        // Read when running, so a token refreshed after the post was queued is used
        $accessToken = PublishingAccount::threads()?->access_token;

        if ($accessToken === null) {
            $this->fail(new RuntimeException('The Threads account was disconnected before the post was published.'));

            return;
        }

        $container = $threads->containerStatus($this->containerId, $accessToken);

        match ($container['status']) {
            'PUBLISHED' => null,
            'IN_PROGRESS' => $this->release(self::SECONDS_BETWEEN_STATUS_CHECKS),
            'FINISHED' => $this->publish($threads, $accessToken),
            default => $this->fail(new RuntimeException(sprintf(
                'Threads container %s is %s: %s',
                $this->containerId,
                $container['status'] ?? 'missing',
                $container['error_message'] ?? '',
            ))),
        };
    }

    private function publish(ThreadsClient $threads, string $accessToken): void
    {
        try {
            $published = $threads->publishContainer($this->userId, $this->containerId, $accessToken);
        } catch (ConnectionException $exception) {
            if ($threads->containerStatus($this->containerId, $accessToken)['status'] === 'PUBLISHED') {
                logger()->warning('Threads published the post without answering in time; its Instagram story share is unknown.', ['container_id' => $this->containerId]);

                return;
            }

            // A retry checks the container again first, so it only publishes if this attempt didn't
            throw $exception;
        }

        if ($this->isSharedToInstagram === true && $published['crossreshare_to_ig_status'] !== 'SUCCESS') {
            logger()->warning('The Threads post could not be shared to the Instagram story.', ['post_id' => $published['id']]);
        }
    }
}
