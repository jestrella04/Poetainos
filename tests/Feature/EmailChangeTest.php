<?php

use App\Models\User;
use App\Notifications\EmailChangeRequested;
use App\Notifications\VerifyEmailCode;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\travel;

/**
 * Ask to move the user's account to a new address through the profile form,
 * and return the code emailed to that address.
 */
function requestEmailChange(User $user, string $newEmail): string
{
    Notification::fake();
    actingAs($user)->put(route('users.update', $user->username), [
        'name' => fake()->name(),
        'email' => $newEmail,
    ]);

    // actingAs() keeps this instance as the signed-in user, so it must see the pending address
    $user->refresh();

    return sentEmailChangeCode($newEmail);
}

function sentEmailChangeCode(string $address): string
{
    $code = '';
    Notification::assertSentOnDemand(VerifyEmailCode::class, function (VerifyEmailCode $notification, array $channels, AnonymousNotifiable $notifiable) use ($address, &$code): bool {
        $code = $notification->code;

        return $notifiable->routeNotificationFor('mail') === $address;
    });

    return $code;
}

describe('asking to change the email address', function (): void {
    it('keeps the current address until the new one is confirmed', function (): void {
        // Given
        Notification::fake();
        $user = createUser();
        $currentEmail = $user->email;
        $newEmail = fake()->unique()->safeEmail();

        // When
        $response = actingAs($user)->put(route('users.update', $user->username), [
            'name' => fake()->name(),
            'email' => $newEmail,
        ]);

        // Then
        $response->assertRedirect(route('users.account'))->assertInertiaFlash('message', 'accounts.email-change-pending');
        $user->refresh();
        expect($user->email)->toBe($currentEmail);
        expect($user->email_verified_at)->not->toBeNull();
        expect($user->pending_email)->toBe($newEmail);
    });

    it('emails a code to the new address and warns the current one', function (): void {
        // Given
        $user = createUser();
        $newEmail = fake()->unique()->safeEmail();

        // When
        requestEmailChange($user, $newEmail);

        // Then
        Notification::assertSentTo($user, EmailChangeRequested::class, fn (EmailChangeRequested $notification): bool => $notification->newEmail === $newEmail);
        Notification::assertNotSentTo($user, VerifyEmailCode::class);
    });

    it('shows the pending address on the account page', function (): void {
        // Given
        $user = createUser();
        $newEmail = fake()->unique()->safeEmail();
        requestEmailChange($user, $newEmail);

        // When
        $response = actingAs($user)->get(route('users.account'));

        // Then
        $response->assertInertia(fn ($page) => $page->where('account.pending_email', $newEmail));
    });
});

describe('confirming a new email address', function (): void {
    it('moves the account to the new address once its code is entered', function (): void {
        // Given
        $user = createUser();
        $newEmail = fake()->unique()->safeEmail();
        $code = requestEmailChange($user, $newEmail);

        // When
        $response = actingAs($user)->post(route('users.email.verify'), ['code' => $code]);

        // Then
        $response->assertRedirect(route('users.account'))->assertInertiaFlash('message', 'accounts.email-changed');
        $user->refresh();
        expect($user->email)->toBe($newEmail);
        expect($user->pending_email)->toBeNull();
        expect($user->email_verified_at)->not->toBeNull();
    });

    it('keeps the current address when the code is wrong', function (): void {
        // Given
        $user = createUser();
        $currentEmail = $user->email;
        $code = requestEmailChange($user, fake()->unique()->safeEmail());

        // When
        $response = actingAs($user)->postJson(route('users.email.verify'), ['code' => wrongCodeFor($code)]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'code' => __('The verification code is invalid or has expired.'),
        ]);
        expect($user->refresh()->email)->toBe($currentEmail);
    });

    it('keeps the current address when the code has expired', function (): void {
        // Given
        $user = createUser();
        $currentEmail = $user->email;
        $code = requestEmailChange($user, fake()->unique()->safeEmail());
        travel(16)->minutes();

        // When
        $response = actingAs($user)->postJson(route('users.email.verify'), ['code' => $code]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('code');
        expect($user->refresh()->email)->toBe($currentEmail);
    });

    it('refuses an address another account took in the meantime', function (): void {
        // Given
        $user = createUser();
        $currentEmail = $user->email;
        $newEmail = fake()->unique()->safeEmail();
        $code = requestEmailChange($user, $newEmail);
        createUser(['email' => $newEmail]);

        // When
        $response = actingAs($user)->postJson(route('users.email.verify'), ['code' => $code]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'code' => __('That email address is already in use by another account.'),
        ]);
        $user->refresh();
        expect($user->email)->toBe($currentEmail);
        expect($user->pending_email)->toBeNull();
    });

    it('refuses a code when no change awaits confirmation', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->postJson(route('users.email.verify'), ['code' => fake()->numerify('######')]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'code' => __('There is no email address change awaiting confirmation.'),
        ]);
    });
});

describe('resending and cancelling an email change', function (): void {
    it('emails a fresh code to the pending address and stops accepting the previous one', function (): void {
        // Given
        $user = createUser();
        $newEmail = fake()->unique()->safeEmail();
        $previousCode = requestEmailChange($user, $newEmail);
        Notification::fake();

        // When
        actingAs($user)->post(route('users.email.resend'))->assertNoContent();

        // Then
        $freshCode = sentEmailChangeCode($newEmail);
        actingAs($user)->postJson(route('users.email.verify'), ['code' => $previousCode])->assertUnprocessable();
        actingAs($user)->post(route('users.email.verify'), ['code' => $freshCode])->assertRedirect(route('users.account'));
        expect($user->refresh()->email)->toBe($newEmail);
    });

    it('keeps the current address and drops the pending one when cancelled', function (): void {
        // Given
        $user = createUser();
        $currentEmail = $user->email;
        $code = requestEmailChange($user, fake()->unique()->safeEmail());

        // When
        $response = actingAs($user)->delete(route('users.email.cancel'));

        // Then
        $response->assertRedirect(route('users.account'))->assertInertiaFlash('message', 'accounts.email-change-cancelled');
        $user->refresh();
        expect($user->pending_email)->toBeNull();
        actingAs($user)->postJson(route('users.email.verify'), ['code' => $code])->assertUnprocessable();
        expect($user->refresh()->email)->toBe($currentEmail);
    });
});
