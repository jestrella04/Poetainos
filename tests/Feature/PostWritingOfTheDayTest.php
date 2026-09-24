<?php

use App\Models\DailySelection;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\Channels\FacebookPageChannel;
use App\Notifications\WritingOfTheDayPosted;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    config([
        'services.facebook.page_id' => (string) fake()->randomNumber(9),
        'services.facebook.page_access_token' => fake()->sha256(),
        'services.facebook.graph_version' => 'v23.0',
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

    it('sends nothing when the Facebook Page is not configured', function (string $missingKey): void {
        // Given
        Notification::fake();
        Writing::factory()->create();
        config(["services.facebook.{$missingKey}" => null]);

        // When
        pendingArtisan('writing:post-of-the-day')->assertSuccessful();

        // Then
        Notification::assertNothingSent();
    })->with(['page_id', 'page_access_token']);

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
