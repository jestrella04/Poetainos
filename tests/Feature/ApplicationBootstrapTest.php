<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Laravel\actingAs;

describe('the web middleware group', function (): void {
    it('wraps the response in security headers and shares the appearance before Inertia renders', function (): void {
        // When
        $web = app('router')->getMiddlewareGroups()['web'];
        $position = array_flip($web);

        // Then
        expect($web[0])->toBe(SecurityHeaders::class)
            ->and($position[HandleAppearance::class])->toBeLessThan($position[HandleInertiaRequests::class]);
    });
});

describe('the guest middleware', function (): void {
    it('sends signed-in users to the home page', function (): void {
        // When
        $response = actingAs(createUser())->get(route('login'));

        // Then
        $response->assertRedirect(route('home'));
    });
});

describe('the api middleware group', function (): void {
    it('throttles requests with the api rate limiter', function (): void {
        // When
        $api = app('router')->getMiddlewareGroups()['api'];

        // Then
        expect($api)->toContain('throttle:api')
            ->and(RateLimiter::limiter('api'))->not->toBeNull();
    });
});

describe('a new registration', function (): void {
    it('is verified by exactly one listener', function (): void {
        // When
        $listeners = Event::getListeners(Registered::class);

        // Then
        expect($listeners)->toHaveCount(1);
    });
});

describe('the schedule', function (): void {
    it('runs the daily maintenance and featured content commands', function (): void {
        // When
        $commands = collect(app(Schedule::class)->events())->map(fn ($event) => $event->command)->implode("\n");

        // Then
        expect($commands)->toContain('aura:update', 'karma:update', 'sitemap:generate', 'writing:pick-of-the-day', 'writing:post-of-the-day', 'author:random', 'category:random');
    });
});
