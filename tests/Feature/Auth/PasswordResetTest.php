<?php

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('resetting a password', function (): void {
    // The GET /forgot-password "screen" route is commented out in routes/auth.php —
    // only POST /forgot-password (password.email) exists in this app.
    it('can request a reset password link', function (): void {
        // Given
        Notification::fake();
        $user = createUser();

        // When
        post('/forgot-password', ['email' => $user->email]);

        // Then
        Notification::assertSentTo($user, ResetPassword::class);
    });

    it('can render the reset password screen', function (): void {
        // Given
        Notification::fake();
        $user = createUser();
        post('/forgot-password', ['email' => $user->email]);

        // Then
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            // When
            $response = get('/reset-password/'.$notification->token);

            // Then
            $response->assertStatus(200);

            return true;
        });
    });

    it('can be reset with a valid token', function (): void {
        // Given
        Notification::fake();
        $user = createUser();
        post('/forgot-password', ['email' => $user->email]);
        $newPassword = fakeStrongPassword();

        // Then
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $newPassword) {
            // When
            $response = post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

            // Then
            $response->assertSessionHasNoErrors();

            return true;
        });
    });

    it('is throttled so it cannot be used to mail-bomb arbitrary addresses', function (): void {
        // When
        foreach (range(1, 5) as $attempt) {
            post('/forgot-password', ['email' => fake()->unique()->safeEmail()]);
        }
        $response = post('/forgot-password', ['email' => fake()->unique()->safeEmail()]);

        // Then
        $response->assertTooManyRequests();
    });
});
