<?php

use App\Models\DailySelection;
use App\Models\PublishingAccount;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\Channels\FacebookPageChannel;
use App\Notifications\Channels\ThreadsChannel;
use App\Notifications\WritingOfTheDayPosted;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Sleep;

beforeEach(function (): void {
    config([
        'services.facebook.page_id' => (string) fake()->randomNumber(9),
        'services.facebook.page_access_token' => fake()->sha256(),
        'services.facebook.graph_version' => 'v23.0',
        'services.threads.api_version' => 'v1.0',
    ]);
});

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
        Sleep::fake();
    });

    it('creates a text container with the message and link, then publishes it once it is finished, sharing it to the Instagram story', function (): void {
        // Given
        Http::preventStrayRequests();
        $account = PublishingAccount::factory()->create();
        Http::fake([
            "graph.threads.net/v1.0/{$account->account_id}/threads" => Http::response(['id' => '1789']),
            'graph.threads.net/v1.0/1789*' => Http::sequence()
                ->push(['status' => 'IN_PROGRESS', 'id' => '1789'])
                ->push(['status' => 'FINISHED', 'id' => '1789']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::response(['id' => '1790', 'crossreshare_to_ig_status' => 'SUCCESS']),
        ]);
        $author = User::factory()->create();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted($writing));

        // Then
        Http::assertSentInOrder([
            fn (Request $request): bool => $request->method() === 'POST'
                && $request->url() === "https://graph.threads.net/v1.0/{$account->account_id}/threads"
                && $request['media_type'] === 'TEXT'
                && $request['link_attachment'] === $writing->path()
                && $request['crossreshare_to_ig'] === 'true'
                && $request['access_token'] === $account->access_token
                && str_contains($request['text'], $writing->title)
                && str_contains($request['text'], $author->getName()),
            fn (Request $request): bool => $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://graph.threads.net/v1.0/1789?'),
            fn (Request $request): bool => $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://graph.threads.net/v1.0/1789?'),
            fn (Request $request): bool => $request->method() === 'POST'
                && $request->url() === "https://graph.threads.net/v1.0/{$account->account_id}/threads_publish"
                && $request['creation_id'] === '1789'
                && $request['access_token'] === $account->access_token,
        ]);
    });

    it('keeps the Threads post and logs a warning when the Instagram story share fails', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            "graph.threads.net/v1.0/{$account->account_id}/threads" => Http::response(['id' => '1789']),
            'graph.threads.net/v1.0/1789*' => Http::response(['status' => 'FINISHED']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::response(['id' => '1790', 'crossreshare_to_ig_status' => 'FAILED']),
        ]);
        $log = Log::spy();

        // When
        Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted(Writing::factory()->create()));

        // Then
        $log->shouldHaveReceived('warning')->once();
    });

    it('publishes the post without the Instagram story share, logging a warning, when Threads rejects the container with it', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            "graph.threads.net/v1.0/{$account->account_id}/threads" => fn (Request $request) => isset($request['crossreshare_to_ig'])
                ? Http::response(['error' => ['code' => 1, 'message' => 'An unknown error occurred']], 500)
                : Http::response(['id' => '1789']),
            'graph.threads.net/v1.0/1789*' => Http::response(['status' => 'FINISHED']),
            "graph.threads.net/v1.0/{$account->account_id}/threads_publish" => Http::response(['id' => '1790']),
        ]);
        $log = Log::spy();

        // When
        Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted(Writing::factory()->create()));

        // Then
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/threads_publish')
            && $request['creation_id'] === '1789');
        $log->shouldHaveReceived('warning')->once();
    });

    it('fails when the Threads API rejects the post, so the queued job is retried', function (): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake(["graph.threads.net/v1.0/{$account->account_id}/threads" => Http::response(['error' => ['message' => 'Invalid token']], 400)]);
        $writing = Writing::factory()->create();

        // When
        $post = fn () => Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted($writing));

        // Then
        expect($post)->toThrow(RequestException::class);
    });

    it('fails without publishing when the container is not finished, so the queued job is retried', function (array $containerResponse): void {
        // Given
        $account = PublishingAccount::factory()->create();
        Http::fake([
            "graph.threads.net/v1.0/{$account->account_id}/threads" => Http::response(['id' => '1789']),
            'graph.threads.net/v1.0/1789*' => Http::response($containerResponse),
        ]);
        $writing = Writing::factory()->create();

        // When
        $post = fn () => Notification::route(ThreadsChannel::class, $account->account_id)
            ->notifyNow(new WritingOfTheDayPosted($writing));

        // Then
        expect($post)->toThrow(RuntimeException::class);
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/threads_publish'));
    })->with([
        'failed' => [['status' => 'ERROR', 'error_message' => 'LINK_ATTACHMENT_URL_UNAVAILABLE']],
        'still in progress' => [['status' => 'IN_PROGRESS']],
    ]);
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
