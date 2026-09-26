<?php

use function Pest\Laravel\actingAs;

describe('confirming a password', function (): void {
    // The GET /confirm-password "screen" route is commented out in routes/auth.php —
    // only POST /confirm-password (named password.confirmer) exists in this app.
    it('can be confirmed', function (): void {
        // Given
        $password = fake()->password();
        $user = createUserWithPassword($password);

        // When
        $response = actingAs($user)->postJson('/confirm-password', [
            'password' => $password,
        ]);

        // Then
        $response->assertNoContent();
        expect(session('auth.password_confirmed_at'))->not->toBeNull();
    });

    it('is not confirmed with an invalid password', function (): void {
        // Given
        $password = fake()->password();
        $user = createUserWithPassword($password);

        // When
        $response = actingAs($user)->postJson('/confirm-password', [
            'password' => strrev($password).fake()->password(),
        ]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('password');
    });
});
