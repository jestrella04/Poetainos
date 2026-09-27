<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
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

    private const CONTAINER_STATUS_CHECKS = 6;

    private const SECONDS_BETWEEN_STATUS_CHECKS = 5;

    /**
     * The id of the Threads account the token belongs to.
     */
    public function fetchUserId(string $accessToken): string
    {
        return (string) $this->request()
            ->get($this->versioned('me'), ['fields' => 'id', 'access_token' => $accessToken])
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
        $containerId = $this->request()
            ->asForm()
            ->post($this->versioned("{$userId}/threads"), [
                'media_type' => 'TEXT',
                'text' => $text,
                'link_attachment' => $link,
                'crossreshare_to_ig' => 'true',
                'access_token' => $accessToken,
            ])
            ->throw()
            ->json('id');

        $this->waitUntilContainerFinished($containerId, $accessToken);

        $published = $this->request()
            ->asForm()
            ->post($this->versioned("{$userId}/threads_publish"), [
                'creation_id' => $containerId,
                'access_token' => $accessToken,
            ])
            ->throw();

        if ($published->json('crossreshare_to_ig_status') !== 'SUCCESS') {
            logger()->warning('The Threads post could not be shared to the Instagram story.', ['post_id' => $published->json('id')]);
        }
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

            $container = $this->request()
                ->get($this->versioned($containerId), ['fields' => 'status,error_message', 'access_token' => $accessToken])
                ->throw();

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
     * @param  array<string, mixed>  $query
     * @return array{access_token: string, expires_in: int}
     */
    private function tokenResponse(string $path, array $query): array
    {
        $response = $this->request()->get(self::BASE_URL.'/'.$path, $query)->throw();

        return [
            'access_token' => (string) $response->json('access_token'),
            'expires_in' => (int) $response->json('expires_in'),
        ];
    }

    private function versioned(string $path): string
    {
        return sprintf('%s/%s/%s', self::BASE_URL, config('services.threads.api_version'), $path);
    }

    private function request(): PendingRequest
    {
        return Http::timeout(self::TIMEOUT_SECONDS);
    }
}
