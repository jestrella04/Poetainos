<?php

use App\Models\User;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\post;

describe('registration', function (): void {
    // The GET /register "screen" route is commented out in routes/auth.php —
    // only POST /register (store) exists in this app.
    it('allows new users to register', function (): void {
        // Given
        // RegisteredUserController requires a seeded "user" role and a password
        // matching a custom complexity regex (upper + lower + digit/symbol, 8+ chars).
        $username = fakeUsername();
        $password = fakeStrongPassword();

        // When
        $response = post('/register', [
            'username' => $username,
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
            'service_agreement' => true,
            'privacy_agreement' => true,
        ]);

        // Then
        assertAuthenticated();
        // RegisteredUserController::store() renders the verify-email prompt directly
        // rather than redirecting.
        $response->assertOk();
        expect(User::where('username', $username)->firstOrFail()->role?->name)->toBe('user');
    });

    it('is throttled so it cannot be used for mass account creation', function (): void {
        // When
        // An always-invalid payload keeps every attempt a guest request (a
        // successful one would authenticate and trip the `guest` middleware
        // instead of the throttle being tested here).
        foreach (range(1, 5) as $attempt) {
            post('/register', []);
        }
        $response = post('/register', []);

        // Then
        $response->assertTooManyRequests();
    });
});
