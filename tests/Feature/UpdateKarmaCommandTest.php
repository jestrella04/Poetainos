<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;

describe('the karma:update command', function (): void {
    it('recalculates the karma of every user without any HTTP request', function (): void {
        // Given
        Http::fake();
        $users = User::factory()->count(3)->create();

        // When
        pendingArtisan('karma:update')->assertSuccessful();

        // Then
        $users->each(fn (User $user) => expect($user->refresh()->karma)->toBe('F'));
        Http::assertNothingSent();
    });

    it('leaves the users\' last update time alone', function (): void {
        // Given
        $updatedAt = Carbon::now()->subMonth()->startOfSecond();
        $user = User::factory()->create(['updated_at' => $updatedAt]);

        // When
        pendingArtisan('karma:update')->assertSuccessful();

        // Then
        expect($user->refresh()->updated_at?->toDateTimeString())->toBe($updatedAt->toDateTimeString());
    });

    it('is scheduled daily', function (): void {
        // When
        $events = collect(app(Schedule::class)->events());

        // Then
        expect($events->contains(fn ($event) => $event->command !== null && str_contains($event->command, 'karma:update')))->toBeTrue();
    });
});
