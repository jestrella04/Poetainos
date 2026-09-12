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

        // When
        $response = post('/register', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'service_agreement' => true,
            'privacy_agreement' => true,
        ]);

        // Then
        assertAuthenticated();
        // RegisteredUserController::store() renders the verify-email prompt directly
        // rather than redirecting.
        $response->assertOk();
        expect(User::where('username', 'testuser')->firstOrFail()->role?->name)->toBe('user');
    });
});
