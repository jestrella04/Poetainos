<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use RuntimeException;

/**
 * The calls this site makes to the Threads API: checking and renewing the
 * access token of its Threads account, and publishing posts on it. Every call
 * throws on an error response, so a queued post is retried.
 */
class ThreadsClient
{
    private const BASE_URL = 'https://graph.threads.net';

    private const TIMEOUT_SECONDS = 15;

    /**
     * Publishing takes longer than the other calls: Threads shares the post to
     * the Instagram story before answering.
     */
    private const PUBLISH_TIMEOUT_SECONDS = 45;

    private const CONTAINER_STATUS_CHECKS = 6;

    private const SECONDS_BETWEEN_STATUS_CHECKS = 5;

    /**
     * The id of the Threads account the token belongs to.
     */
    public function fetchUserId(string $accessToken): string
    {
        return (string) $this->request($accessToken)
            ->get($this->versioned('me'), ['fields' => 'id'])
            ->throw()
            ->json('id');
    }

    /**
     * Trade a short-lived token (one hour) for a long-lived one (60 days).
     *
     * @return array{access_token: string, expires_in: int}
     */
    public function exchangeForLongLived(string $shortLivedToken): array
    {
        return $this->tokenResponse('access_token', [
            'grant_type' => 'th_exchange_token',
            'client_secret' => config('services.threads.app_secret'),
            'access_token' => $shortLivedToken,
        ]);
    }

    /**
     * Renew a long-lived token that is at least a day old and not expired yet.
     *
     * @return array{access_token: string, expires_in: int}
     */
    public function refresh(string $longLivedToken): array
    {
        return $this->tokenResponse('refresh_access_token', [
            'grant_type' => 'th_refresh_token',
            'access_token' => $longLivedToken,
        ]);
    }

    /**
     * Publish a text post with a link preview: Threads first creates a media
     * container, then publishes it once it has finished processing. Threads
     * also shares the post to the linked Instagram account's story; that share
     * failing doesn't stop the post, so it is only logged.
     */
    public function publishText(string $userId, string $accessToken, string $text, string $link): void
    {
        $isSharedToInstagram = true;

        try {
            $containerId = $this->createTextContainer($userId, $accessToken, $text, $link, $isSharedToInstagram);
        } catch (RequestException $exception) {
            // Threads rejects the whole container, with an unknown error, when the Instagram account can't take the share
            logger()->warning('Threads rejected the post with the Instagram story share, so it is posted without it.', ['error' => $exception->response->json('error')]);
            $isSharedToInstagram = false;
            $containerId = $this->createTextContainer($userId, $accessToken, $text, $link, $isSharedToInstagram);
        }

        $this->waitUntilContainerFinished($containerId, $accessToken);

        try {
            $published = $this->request($accessToken, self::PUBLISH_TIMEOUT_SECONDS)
                ->asForm()
                ->post($this->versioned("{$userId}/threads_publish"), [
                    'creation_id' => $containerId,
                ])
                ->throw();
        } catch (ConnectionException $exception) {
            // Failing here would retry the job, and post the writing twice, when Threads published it without answering in time
            if ($this->isContainerPublished($containerId, $accessToken) === true) {
                logger()->warning('Threads published the post without answering in time; its Instagram story share is unknown.', ['container_id' => $containerId]);

                return;
            }

            throw $exception;
        }

        if ($isSharedToInstagram === true && $published->json('crossreshare_to_ig_status') !== 'SUCCESS') {
            logger()->warning('The Threads post could not be shared to the Instagram story.', ['post_id' => $published->json('id')]);
        }
    }

    /**
     * The id of a new text container with a link preview.
     */
    private function createTextContainer(string $userId, string $accessToken, string $text, string $link, bool $isSharedToInstagram): string
    {
        $instagramShare = $isSharedToInstagram === true ? ['crossreshare_to_ig' => 'true'] : [];

        return (string) $this->request($accessToken)
            ->asForm()
            ->post($this->versioned("{$userId}/threads"), [
                'media_type' => 'TEXT',
                'text' => $text,
                'link_attachment' => $link,
                ...$instagramShare,
            ])
            ->throw()
            ->json('id');
    }

    /**
     * Threads builds a container asynchronously (fetching the link preview
     * takes a few seconds), and publishing it before it is finished fails
     * with "The requested resource does not exist".
     */
    private function waitUntilContainerFinished(string $containerId, string $accessToken): void
    {
        for ($check = 1; $check <= self::CONTAINER_STATUS_CHECKS; $check++) {
            Sleep::for(self::SECONDS_BETWEEN_STATUS_CHECKS)->seconds();

            $container = $this->fetchContainer($containerId, $accessToken);
            $status = $container->json('status');

            if ($status === 'FINISHED') {
                return;
            }

            if ($status !== 'IN_PROGRESS') {
                throw new RuntimeException(sprintf('Threads container %s is %s: %s', $containerId, $status, $container->json('error_message')));
            }
        }

        throw new RuntimeException(sprintf('Threads container %s is still in progress', $containerId));
    }

    /**
     * Whether a publish request that got no answer went through anyway,
     * checked after giving Threads a few more seconds to finish it.
     */
    private function isContainerPublished(string $containerId, string $accessToken): bool
    {
        Sleep::for(self::SECONDS_BETWEEN_STATUS_CHECKS)->seconds();

        return $this->fetchContainer($containerId, $accessToken)->json('status') === 'PUBLISHED';
    }

    private function fetchContainer(string $containerId, string $accessToken): Response
    {
        return $this->request($accessToken)
            ->get($this->versioned($containerId), ['fields' => 'status,error_message'])
            ->throw();
    }

    /**
     * The token endpoints only take the token (and the app secret) in the
     * query string. A connection error quotes the full URL, so it is replaced
     * by one naming only the endpoint, keeping the secrets out of the logs.
     *
     * @param  array<string, mixed>  $query
     * @return array{access_token: string, expires_in: int}
     *
     * @throws ConnectionException
     */
    private function tokenResponse(string $path, array $query): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)->get(self::BASE_URL.'/'.$path, $query)->throw();
        } catch (ConnectionException) {
            throw new ConnectionException(sprintf('Threads could not be reached at %s/%s.', self::BASE_URL, $path));
        }

        return [
            'access_token' => (string) $response->json('access_token'),
            'expires_in' => (int) $response->json('expires_in'),
        ];
    }

    private function versioned(string $path): string
    {
        return sprintf('%s/%s/%s', self::BASE_URL, config('services.threads.api_version'), $path);
    }

    /**
     * A request authorized with the token in its header, where it stays out of
     * the URL that connection errors quote and logs record.
     */
    private function request(string $accessToken, int $timeoutSeconds = self::TIMEOUT_SECONDS): PendingRequest
    {
        return Http::timeout($timeoutSeconds)->withToken($accessToken);
    }
}
