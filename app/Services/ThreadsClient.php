<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

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
     * The id of a new text container with a link preview, which Threads builds
     * asynchronously and publishContainer() posts once it is finished. Threads
     * also shares the post to the linked Instagram account's story; when it
     * rejects the container for that (with an unknown error), the container is
     * created again without the share, which is then only logged.
     *
     * @return array{id: string, isSharedToInstagram: bool}
     */
    public function createTextContainer(string $userId, string $accessToken, string $text, string $link): array
    {
        try {
            return ['id' => $this->requestTextContainer($userId, $accessToken, $text, $link, true), 'isSharedToInstagram' => true];
        } catch (RequestException $exception) {
            logger()->warning('Threads rejected the post with the Instagram story share, so it is posted without it.', ['error' => $exception->response->json('error')]);

            return ['id' => $this->requestTextContainer($userId, $accessToken, $text, $link, false), 'isSharedToInstagram' => false];
        }
    }

    /**
     * The status of a container (IN_PROGRESS, FINISHED, PUBLISHED, ERROR or
     * EXPIRED) and, when it failed, why.
     *
     * @return array{status: string|null, error_message: string|null}
     */
    public function containerStatus(string $containerId, string $accessToken): array
    {
        $container = $this->request($accessToken)
            ->get($this->versioned($containerId), ['fields' => 'status,error_message'])
            ->throw();

        return [
            'status' => $container->json('status'),
            'error_message' => $container->json('error_message'),
        ];
    }

    /**
     * Publish a finished container, returning the post id and the status of
     * its Instagram story share.
     *
     * @return array{id: string, crossreshare_to_ig_status: string|null}
     *
     * @throws ConnectionException when Threads doesn't answer in time, which doesn't tell whether it published.
     */
    public function publishContainer(string $userId, string $containerId, string $accessToken): array
    {
        $published = $this->request($accessToken, self::PUBLISH_TIMEOUT_SECONDS)
            ->asForm()
            ->post($this->versioned("{$userId}/threads_publish"), [
                'creation_id' => $containerId,
            ])
            ->throw();

        return [
            'id' => (string) $published->json('id'),
            'crossreshare_to_ig_status' => $published->json('crossreshare_to_ig_status'),
        ];
    }

    private function requestTextContainer(string $userId, string $accessToken, string $text, string $link, bool $isSharedToInstagram): string
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
