<?php

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;

describe('the karma:update command', function (): void {
    it('recalculates the karma of every user without any HTTP request', function (): void {
        // Given
        Http::fake();
        $users = User::factory()->count(3)->create();

        // When
        $this->artisan('karma:update')->assertSuccessful();

        // Then
        $users->each(fn (User $user) => expect($user->fresh()->karma)->toBe('F'));
        Http::assertNothingSent();
    });

    it('is scheduled daily', function (): void {
        // When
        $events = collect(app(Schedule::class)->events());

        // Then
        expect($events->contains(fn ($event) => str_contains($event->command, 'karma:update')))->toBeTrue();
    });
});
