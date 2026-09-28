<?php

use App\Jobs\PublishThreadsContainer;
use App\Models\DailySelection;
use App\Models\PublishingAccount;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\Channels\FacebookPageChannel;
use App\Notifications\Channels\ThreadsChannel;
use App\Notifications\WritingOfTheDayPosted;
use App\Services\ThreadsClient;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    config([
        'services.facebook.page_id' => (string) fake()->randomNumber(9),
        'services.facebook.page_access_token' => fake()->sha256(),
        'services.facebook.graph_version' => 'v23.0',
        'services.threads.api_version' => 'v1.0',
    ]);
});

/**
 * Whether the request carries the token in its Authorization header and
 * nowhere in its URL or body, where logs and error messages could quote it.
 */
function isAuthorizedByHeader(Request $request, string $accessToken): bool
{
    return $request->hasHeader('Authorization', 'Bearer '.$accessToken)
        && str_contains($request->url(), $accessToken) === false
        && str_contains($request->body(), $accessToken) === false;
}

describe('the writing:post-of-the-day command', function (): void {
    it('sends today\'s pick to the Facebook Page', function (): void {
        // Given
        Notification::fake();
        Writing::factory()->create();

        // When
        pendingArtisan('writing:post-of-the-day')->assertSuccessful();

        // Then
        $pickId = DailySelection::current()?->writing_id;
        Notification::assertSentOnDemand(
            WritingOfTheDayPosted::class,
            fn (WritingOfTheDayPosted $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routeNotificationFor(FacebookPageChannel::class) === config('services.facebook.page_id')
                && $notification->toFacebookPage($notifiable)['link'] === Writing::find($pickId)?->path(),
        );
    });

    it('sends nothing while there are no writings', function (): void {
        // Given
        Notification::fake();

        // When
        $command = pendingArtisan('writing:post-of-the-day');

        // Then
        $command->expectsOutputToContain('nothing was posted')->assertSuccessful();
        Notification::assertNothingSent();
    });

    it('sends nothing when no network is configured', function (string $missingKey): void {
        // Given
        Notification::fake();
        Writing::factory()->create();
        config(["services.facebook.{$missingKey}" => null]);

        // When
        pendingArtisan('writing:post-of-the-day')->assertSuccessful();

        // Then
        Notification::assertNothingSent();
    })->with(['page_id', 'page_access_token']);

    it('sends today\'s pick to the connected Threads account only, when the Facebook Page is not configured', function (): void {
        // Given
        Notification::fake();
        Writing::factory()->create();
        config(['services.facebook.page_id' => null]);
        $account = PublishingAccount::factory()->create();

        // When
        pendingArtisan('writing:post-of-the-day')->assertSuccessful();

        // Then
        Notification::assertSentOnDemand(
            WritingOfTheDayPosted::class,
            fn (WritingOfTheDayPosted $notification, array $channels, AnonymousNotifiable $notifiable): bool => $channels === [ThreadsChannel::class]
                && $notifiable->routeNotificationFor(ThreadsChannel::class) === $account->account_id,
        );
    });

    it('sends today\'s pick to both networks when both are configured', function (): void {
        // Given
        Notification::fake();
        Writing::factory()->create();
        PublishingAccount::factory()->create();

        // When
        pendingArtisan('writing:post-of-the-day')->assertSuccessful();

        // Then
        Notification::assertSentOnDemand(
            WritingOfTheDayPosted::class,
            fn (WritingOfTheDayPosted $notification, array $channels): bool => $channels === [FacebookPageChannel::class, ThreadsChannel::class],
        );
    });

    it('is scheduled daily', function (): void {
        // When
        $events = collect(app(Schedule::class)->events());

        // Then
        expect($events->contains(fn ($event) => $event->command !== null && str_contains($event->command, 'writing:post-of-the-day')))->toBeTrue();
    });
});

describe('posting the writing of the day on the Facebook Page', function (): void {
    it('is queued', function (): void {
        // Then
        expect(new WritingOfTheDayPosted(Writing::factory()->make()))->toBeInstanceOf(ShouldQueue::class);
    });

    it('publishes the title, author, excerpt and link on the page feed', function (): void {
        // Given
        Http::fake(['graph.facebook.com/*' => Http::response(['id' => '123_456'])]);
        $author = User::factory()->create();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        Notification::route(FacebookPageChannel::class, config('services.facebook.page_id'))
            ->notifyNow(new WritingOfTheDayPosted($writing));

        // Then
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === sprintf('https://graph.facebook.com/v23.0/%s/feed', config('services.facebook.page_id'))
            && $request['link'] === $writing->path()
            && $request['access_token'] === config('services.facebook.page_access_token')
            && str_contains($request['message'], $writing->title)
            && str_contains($request['message'], $author->getName())
            && str_contains($request['message'], $writing->excerpt()));
    });

    it('fails when the Graph API rejects the post, so the queued job is retried', function (): void {
        // Given
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 400)]);
        $writing = Writing::factory()->create();

        // When
        $post = fn () => Notification::route(FacebookPageChannel::class, config('services.facebook.page_id'))
            ->notifyNow(new WritingOfTheDayPosted($writing));

        // Then
        expect($post)->toThrow(RequestException::class);
    });
});

describe('posting the writing of the day on Threads', function (): void {
    beforeEach(function (): void {
        Queue::fake();
    });

    it('creates a text container with the message and link, shared to the Instagram story, and queues publishing it', function (): void {
        // Given
        Http::preventStrayRequests();
        $account = PublishingAccount::factory()->create();
        Http::fake(["graph.threads.net/v1.0/{$account->account_id}/threads" => Http::response(['id' => '1789'])]);
        $author = User::factory()->create();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted($writing));

        // Then
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === "https://graph.threads.net/v1.0/{$account->account_id}/threads"
            && $request['media_type'] === 'TEXT'
            && $request['link_attachment'] === $writing->path()
            && $request['crossreshare_to_ig'] === 'true'
            && isAuthorizedByHeader($request, $account->access_token)
            && str_contains($request['text'], $writing->title)
            && str_contains($request['text'], $author->getName()));
        Queue::assertPushed(PublishThreadsContainer::class, fn (PublishThreadsContainer $job): bool => $job->userId === $account->account_id
            && $job->containerId === '1789'
            && $job->isSharedToInstagram === true
            && $job->delay === PublishThreadsContainer::SECONDS_BETWEEN_STATUS_CHECKS);
    });

    it('creates the container without the Instagram story share, logging a warning, when Threads rejects it with the share', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            "graph.threads.net/v1.0/{$account->account_id}/threads" => fn (Request $request) => isset($request['crossreshare_to_ig'])
                ? Http::response(['error' => ['code' => 1, 'message' => 'An unknown error occurred']], 500)
                : Http::response(['id' => '1789']),
        ]);
        $log = Log::spy();

        // When
        Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted(Writing::factory()->create()));

        // Then
        Queue::assertPushed(PublishThreadsContainer::class, fn (PublishThreadsContainer $job): bool => $job->containerId === '1789'
            && $job->isSharedToInstagram === false);
        $log->shouldHaveReceived('warning')->once();
    });

    it('fails without queueing anything when the Threads API rejects the post, so the queued notification is retried', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake(["graph.threads.net/v1.0/{$account->account_id}/threads" => Http::response(['error' => ['message' => 'Invalid token']], 400)]);
        $writing = Writing::factory()->create();

        // When
        $post = fn () => Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted($writing));

        // Then
        expect($post)->toThrow(RequestException::class);
        Queue::assertNothingPushed();
    });
});

/**
 * A publish job for the account's container 1789, ready to run with its queue interactions faked.
 */
function threadsPublishJob(PublishingAccount $account, bool $isSharedToInstagram = true): PublishThreadsContainer
{
    return (new PublishThreadsContainer($account->account_id, '1789', $isSharedToInstagram))->withFakeQueueInteractions();
}

describe('publishing a Threads container', function (): void {
    it('publishes the container once it is finished', function (): void {
        // Given
        Http::preventStrayRequests();
        $account = PublishingAccount::factory()->create();
        Http::fake([
            'graph.threads.net/v1.0/1789*' => Http::response(['status' => 'FINISHED', 'id' => '1789']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::response(['id' => '1790', 'crossreshare_to_ig_status' => 'SUCCESS']),
        ]);
        $job = threadsPublishJob($account);

        // When
        $job->handle(app(ThreadsClient::class));

        // Then
        $job->assertNotReleased()->assertNotFailed();
        Http::assertSentInOrder([
            fn (Request $request): bool => $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://graph.threads.net/v1.0/1789?')
                && isAuthorizedByHeader($request, $account->access_token),
            fn (Request $request): bool => $request->method() === 'POST'
                && $request->url() === "https://graph.threads.net/v1.0/{$account->account_id}/threads_publish"
                && $request['creation_id'] === '1789'
                && isAuthorizedByHeader($request, $account->access_token),
        ]);
    });

    it('checks again a few seconds later, without publishing, while the container is being built', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake(['graph.threads.net/v1.0/1789*' => Http::response(['status' => 'IN_PROGRESS'])]);
        $job = threadsPublishJob($account);

        // When
        $job->handle(app(ThreadsClient::class));

        // Then
        $job->assertReleased(PublishThreadsContainer::SECONDS_BETWEEN_STATUS_CHECKS);
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/threads_publish'));
    });

    it('leaves an already published container alone, so a retry never posts twice', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake(['graph.threads.net/v1.0/1789*' => Http::response(['status' => 'PUBLISHED'])]);
        $job = threadsPublishJob($account);

        // When
        $job->handle(app(ThreadsClient::class));

        // Then
        $job->assertNotReleased()->assertNotFailed();
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/threads_publish'));
    });

    it('fails without publishing when Threads could not build the container', function (string $status): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake(['graph.threads.net/v1.0/1789*' => Http::response(['status' => $status, 'error_message' => 'LINK_ATTACHMENT_URL_UNAVAILABLE'])]);
        $job = threadsPublishJob($account);

        // When
        $job->handle(app(ThreadsClient::class));

        // Then
        $job->assertFailed();
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/threads_publish'));
    })->with(['ERROR', 'EXPIRED']);

    it('fails when the Threads account was disconnected', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        $job = threadsPublishJob($account);
        $account->delete();
        Http::fake();

        // When
        $job->handle(app(ThreadsClient::class));

        // Then
        $job->assertFailed();
        Http::assertNothingSent();
    });

    it('keeps the Threads post and logs a warning when the Instagram story share fails', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            'graph.threads.net/v1.0/1789*' => Http::response(['status' => 'FINISHED']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::response(['id' => '1790', 'crossreshare_to_ig_status' => 'FAILED']),
        ]);
        $log = Log::spy();

        // When
        threadsPublishJob($account)->handle(app(ThreadsClient::class));

        // Then
        $log->shouldHaveReceived('warning')->once();
    });

    it('does not warn about the Instagram story share when the container was created without it', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            'graph.threads.net/v1.0/1789*' => Http::response(['status' => 'FINISHED']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::response(['id' => '1790']),
        ]);
        $log = Log::spy();

        // When
        threadsPublishJob($account, isSharedToInstagram: false)->handle(app(ThreadsClient::class));

        // Then
        $log->shouldNotHaveReceived('warning');
    });

    it('succeeds when the publish request times out but Threads published the post', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            'graph.threads.net/v1.0/1789*' => Http::sequence()
                ->push(['status' => 'FINISHED'])
                ->push(['status' => 'PUBLISHED']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::failedConnection(),
        ]);
        $log = Log::spy();
        $job = threadsPublishJob($account);

        // When
        $job->handle(app(ThreadsClient::class));

        // Then
        $job->assertNotFailed();
        $log->shouldHaveReceived('warning')->once();
    });

    it('fails when the publish request times out and Threads did not publish the post, so the job is retried', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            'graph.threads.net/v1.0/1789*' => Http::response(['status' => 'FINISHED']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::failedConnection(),
        ]);

        // When
        $publish = fn () => threadsPublishJob($account)->handle(app(ThreadsClient::class));

        // Then
        expect($publish)->toThrow(ConnectionException::class);
    });
});

describe('the writing of the day post', function (): void {
    it('names the site as configured, as a spaceless hashtag too', function (): void {
        // Given
        config(['poetainos.name' => 'Casa de Letras']);
        $writing = Writing::factory()->create();

        // When
        $message = (new WritingOfTheDayPosted($writing))->toFacebookPage(null)['message'];

        // Then
        expect($message)->toContain('Casa de Letras')->toContain('#CasadeLetras');
    });

    it('fits a writing with a long title on a site with a long name in a Threads post, keeping the title, author and a shortened excerpt', function (): void {
        // Given
        config(['poetainos.name' => trim(str_repeat('Casa de Letras ', 4))]);
        $author = User::factory()->create();
        $writing = Writing::factory()->for($author, 'author')->create([
            'title' => trim(str_repeat('Título ', 14)),
            'text' => str_repeat('palabra ', 200),
        ]);

        $notification = new WritingOfTheDayPosted($writing);

        // When
        $text = $notification->toThreads(null)['text'];

        // Then
        expect(mb_strlen($text))->toBeLessThan(mb_strlen($notification->toFacebookPage(null)['message']));
        $emojiExtraLength = 2 + 2 + 3; // Threads counts ✨, ✨ and 👇 by their UTF-8 bytes
        expect(mb_strlen($text) + $emojiExtraLength)->toBeLessThanOrEqual(500)
            ->and($text)->toContain($writing->title)
            ->toContain($author->getName())
            ->toContain('palabra…”');
    });

    it('posts the same message on Threads as on the Facebook Page when it fits', function (): void {
        // Given
        $notification = new WritingOfTheDayPosted(Writing::factory()->create(['text' => 'Un verso corto.']));

        // When
        $threadsText = $notification->toThreads(null)['text'];

        // Then
        expect($threadsText)->toBe($notification->toFacebookPage(null)['message']);
    });
});
