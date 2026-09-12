<?php

use function Pest\Laravel\actingAs;

// The GET /confirm-password "screen" route is commented out in routes/auth.php —
// only POST /confirm-password (named password.confirmer) exists in this app.
test('password can be confirmed', function (): void {
    $user = createUser();

    $response = actingAs($user)->post('/confirm-password', [
        'password' => 'password',
    ]);

    // ConfirmablePasswordController::store() responds directly with JSON
    // rather than a redirect with flashed session errors.
    $response->assertOk();
    expect(session('auth.password_confirmed_at'))->not->toBeNull();
});

test('password is not confirmed with invalid password', function (): void {
    $user = createUser();

    $response = actingAs($user)->post('/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonStructure(['errors' => ['password']]);
});
