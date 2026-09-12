<?php

use function Pest\Laravel\actingAs;

describe('confirming a password', function (): void {
    // The GET /confirm-password "screen" route is commented out in routes/auth.php —
    // only POST /confirm-password (named password.confirmer) exists in this app.
    it('can be confirmed', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        // Then
        // ConfirmablePasswordController::store() responds directly with JSON
        // rather than a redirect with flashed session errors.
        $response->assertOk();
        expect(session('auth.password_confirmed_at'))->not->toBeNull();
    });

    it('is not confirmed with an invalid password', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['password']]);
    });
});
