<?php

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

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

    it('answers the same whether or not the email has an account', function (): void {
        // Given
        Notification::fake();
        $user = createUser();

        // When
        $known = post('/forgot-password', ['email' => $user->email]);
        $repeated = post('/forgot-password', ['email' => $user->email]);
        $unknown = post('/forgot-password', ['email' => fake()->unique()->safeEmail()]);

        // Then
        foreach ([$known, $repeated, $unknown] as $response) {
            $response->assertRedirect()
                ->assertSessionHasNoErrors()
                ->assertSessionHas('status', __(Password::RESET_LINK_SENT));
        }
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
            $response->assertSessionHasNoErrors()
                ->assertRedirect(route('login', ['isReset' => 1, 'isEmail' => 1]))
                ->assertSessionHas('email', $user->email);

            return true;
        });
    });

    it('refuses a new password weaker than the one registration requires', function (): void {
        // Given
        Notification::fake();
        $user = createUser();
        post('/forgot-password', ['email' => $user->email]);
        $weakPassword = fake()->regexify('[a-z]{10}');

        // Then
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $weakPassword) {
            // When
            $response = post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword,
            ]);

            // Then
            $response->assertSessionHasErrors('password');

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
