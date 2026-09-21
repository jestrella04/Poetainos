<?php

use App\Models\User;
use App\Notifications\VerifyEmailCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

function sendVerificationCode(User $user): string
{
    Notification::fake();
    actingAs($user)->post(route('verification.send'));

    $code = '';
    Notification::assertSentTo($user, VerifyEmailCode::class, function (VerifyEmailCode $notification) use (&$code): bool {
        $code = $notification->code;

        return true;
    });

    return $code;
}

describe('verifying an email', function (): void {
    it('can render the email verification screen', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);

        // When
        $response = actingAs($user)->get('/verify-email');

        // Then
        $response->assertStatus(200);
    });

    it('emails a six digit code when requested', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);

        // When
        $code = sendVerificationCode($user);

        // Then
        expect($code)->toMatch('/^\d{6}$/');
    });

    it('can be verified with the emailed code', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);
        $code = sendVerificationCode($user);
        Event::fake([Verified::class]);

        // When
        $response = actingAs($user)->postJson(route('verification.verify'), ['code' => $code]);

        // Then
        Event::assertDispatched(Verified::class);
        expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
        $response->assertOk()->assertJson(['url' => route('home')]);
    });

    it('is not verified with a wrong code', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);
        $code = sendVerificationCode($user);
        $wrongCode = $code === '000000' ? '111111' : '000000';

        // When
        $response = actingAs($user)->postJson(route('verification.verify'), ['code' => $wrongCode]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('code');
        expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
    });

    it('rejects a malformed code', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);

        // When
        $response = actingAs($user)->postJson(route('verification.verify'), ['code' => 'abc']);

        // Then
        $response->assertJsonValidationErrors('code');
    });

    it('discards the code after too many wrong attempts', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);
        $code = sendVerificationCode($user);
        $wrongCode = $code === '000000' ? '111111' : '000000';
        // The route throttle shares its budget with the resend route; isolate the per-code attempt cap.
        $this->withoutMiddleware(ThrottleRequests::class);

        // When
        foreach (range(1, 5) as $attempt) {
            actingAs($user)->postJson(route('verification.verify'), ['code' => $wrongCode]);
        }
        $response = actingAs($user)->postJson(route('verification.verify'), ['code' => $code]);

        // Then
        $response->assertUnprocessable();
        expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
    });

    it('invalidates the code when the email address changes', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);
        $code = sendVerificationCode($user);
        $user->forceFill(['email' => 'changed@example.com'])->save();

        // When
        $response = actingAs($user)->postJson(route('verification.verify'), ['code' => $code]);

        // Then
        $response->assertUnprocessable();
        expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
    });

    it('cannot reuse a code once it has been consumed', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);
        $code = sendVerificationCode($user);
        actingAs($user)->postJson(route('verification.verify'), ['code' => $code]);
        $user->forceFill(['email_verified_at' => null])->save();

        // When
        $response = actingAs($user)->postJson(route('verification.verify'), ['code' => $code]);

        // Then
        $response->assertUnprocessable();
    });
});
