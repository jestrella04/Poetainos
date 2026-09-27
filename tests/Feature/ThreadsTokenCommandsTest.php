<?php

use App\Models\PublishingAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\freezeTime;
use function Pest\Laravel\travel;

beforeEach(function (): void {
    config([
        'services.threads.app_secret' => fake()->sha1(),
        'services.threads.api_version' => 'v1.0',
    ]);
});

describe('the threads:connect command', function (): void {
    it('stores a long-lived token, encrypted, with the account id it belongs to', function (): void {
        // Given
        Http::preventStrayRequests();
        Http::fake(['graph.threads.net/v1.0/me*' => Http::response(['id' => '28592940'])]);

        // When
        pendingArtisan('threads:connect')
            ->expectsQuestion('Threads access token', 'long-lived-token')
            ->expectsOutput('Connected the Threads account 28592940.')
            ->assertSuccessful();

        // Then
        $account = PublishingAccount::threads();
        expect($account?->account_id)->toBe('28592940')
            ->and($account?->access_token)->toBe('long-lived-token')
            ->and($account?->expires_at)->toBeNull()
            ->and(DB::table('publishing_accounts')->value('access_token'))->not->toBe('long-lived-token');
    });

    it('exchanges a short-lived token for a long-lived one first', function (): void {
        // Given
        Http::preventStrayRequests();
        freezeTime();
        Http::fake([
            'graph.threads.net/access_token*' => Http::response(['access_token' => 'long-lived-token', 'token_type' => 'bearer', 'expires_in' => 5184000]),
            'graph.threads.net/v1.0/me*' => Http::response(['id' => '28592940']),
        ]);

        // When
        pendingArtisan('threads:connect', ['--short-lived' => true])
            ->expectsQuestion('Threads access token', 'short-lived-token')
            ->assertSuccessful();

        // Then
        Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://graph.threads.net/access_token')
            && $request['grant_type'] === 'th_exchange_token'
            && $request['client_secret'] === config('services.threads.app_secret')
            && $request['access_token'] === 'short-lived-token');
        $account = PublishingAccount::threads();
        expect($account?->access_token)->toBe('long-lived-token')
            ->and($account?->expires_at?->toDateTimeString())->toBe(now()->addDays(60)->toDateTimeString());
    });

    it('refuses to exchange a short-lived token without the app secret', function (): void {
        // Given
        Http::preventStrayRequests();
        config(['services.threads.app_secret' => null]);

        // When
        pendingArtisan('threads:connect', ['--short-lived' => true])->assertFailed();

        // Then
        expect(PublishingAccount::threads())->toBeNull();
    });

    it('replaces the token of an already connected account', function (): void {
        // Given
        Http::fake(['graph.threads.net/v1.0/me*' => Http::response(['id' => '28592940'])]);
        PublishingAccount::factory()->create();

        // When
        pendingArtisan('threads:connect')
            ->expectsQuestion('Threads access token', 'new-token')
            ->assertSuccessful();

        // Then
        expect(PublishingAccount::count())->toBe(1)
            ->and(PublishingAccount::threads()?->access_token)->toBe('new-token');
    });
});

describe('the threads:refresh-token command', function (): void {
    it('stores the renewed token and its new expiry', function (): void {
        // Given
        Http::preventStrayRequests();
        $account = PublishingAccount::factory()->create(['access_token' => 'old-token']);
        travel(2)->days();
        Http::fake(['graph.threads.net/refresh_access_token*' => Http::response(['access_token' => 'new-token', 'token_type' => 'bearer', 'expires_in' => 5184000])]);

        // When
        pendingArtisan('threads:refresh-token')->assertSuccessful();

        // Then
        Http::assertSent(fn (Request $request): bool => $request['grant_type'] === 'th_refresh_token'
            && $request['access_token'] === 'old-token');
        $account->refresh();
        expect($account->access_token)->toBe('new-token')
            ->and($account->expires_at?->toDateTimeString())->toBe(now()->addDays(60)->toDateTimeString());
    });

    it('does nothing when no Threads account is connected', function (): void {
        // Given
        Http::preventStrayRequests();

        // When
        pendingArtisan('threads:refresh-token')->assertSuccessful();

        // Then
        Http::assertNothingSent();
    });

    it('does not refresh a token stored less than a day ago', function (): void {
        // Given
        Http::preventStrayRequests();
        PublishingAccount::factory()->create(['access_token' => 'fresh-token']);

        // When
        pendingArtisan('threads:refresh-token')->assertSuccessful();

        // Then
        Http::assertNothingSent();
        expect(PublishingAccount::threads()?->access_token)->toBe('fresh-token');
    });

    it('fails and keeps the current token when Threads rejects the refresh', function (): void {
        // Given
        PublishingAccount::factory()->create(['access_token' => 'old-token']);
        travel(2)->days();
        Http::fake(['graph.threads.net/refresh_access_token*' => Http::response(['error' => ['message' => 'Session has expired']], 400)]);

        // When
        pendingArtisan('threads:refresh-token')->assertFailed();

        // Then
        expect(PublishingAccount::threads()?->access_token)->toBe('old-token');
    });

    it('is scheduled weekly', function (): void {
        // When
        $events = collect(app(Schedule::class)->events());

        // Then
        expect($events->contains(fn ($event) => $event->command !== null
            && str_contains($event->command, 'threads:refresh-token')
            && $event->expression === '0 0 * * 0'))->toBeTrue();
    });
});
