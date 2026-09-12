<?php

use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;

describe('verifying an email', function (): void {
    it('can render the email verification screen', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);

        // When
        $response = actingAs($user)->get('/verify-email');

        // Then
        $response->assertStatus(200);
    });

    it('can be verified', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);
        Event::fake();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // When
        $response = actingAs($user)->get($verificationUrl);

        // Then
        Event::assertDispatched(Verified::class);
        expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
        $response->assertRedirect(RouteServiceProvider::HOME.'?verified=1');
    });

    it('is not verified with an invalid hash', function (): void {
        // Given
        $user = createUser(['email_verified_at' => null]);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        // When
        actingAs($user)->get($verificationUrl);

        // Then
        expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
    });
});
