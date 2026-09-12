<?php

use App\Models\User;
use App\Notifications\ConfirmSocialLogin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

describe('social login', function (): void {
    it('verifies the email once and does not re-verify on a later login', function (): void {
        // Given
        $verifiedAt = Carbon::parse('2020-01-01 00:00:00');
        $user = createUser([
            'email' => 'writer@example.com',
            // Already has an avatar and a linked Google provider, so the callback's
            // avatar-download and confirm-a-new-provider branches are both skipped —
            // this test targets the email-verification branch only.
            'extra_info' => ['avatar' => 'avatars/existing.png', 'linked_providers' => ['google']],
            'email_verified_at' => $verifiedAt,
        ]);
        $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        expect($user->refresh()->email_verified_at?->equalTo($verifiedAt) ?? false)->toBeTrue();
    });

    it('verifies a not-yet-verified email on first login', function (): void {
        // Given
        $user = createUser([
            'email' => 'writer@example.com',
            'extra_info' => ['avatar' => 'avatars/existing.png', 'linked_providers' => ['google']],
            'email_verified_at' => null,
        ]);
        $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        expect($user->refresh()->email_verified_at)->not->toBeNull();
    });

    it('ignores an external redirect target to prevent an open redirect', function (): void {
        // Given
        $user = createUser([
            'email' => 'writer@example.com',
            'extra_info' => ['avatar' => 'avatars/existing.png', 'linked_providers' => ['google']],
        ]);
        $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
        Socialite::fake('google', $socialUser);

        // When
        get('/login/google?redirect=https://evil.example/phish');
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect(route('home'));
    });

    it('requires emailed confirmation before an existing account trusts a new provider', function (): void {
        // Given
        Notification::fake();
        $user = createUser([
            'email' => 'writer@example.com',
            'extra_info' => ['avatar' => 'avatars/existing.png'],
        ]);
        $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect(route('login'));
        assertGuest();
        Notification::assertSentTo($user, ConfirmSocialLogin::class);
    });

    it('logs the user in once they follow the emailed confirmation link', function (): void {
        // Given
        Notification::fake();
        $user = createUser([
            'email' => 'writer@example.com',
            'extra_info' => ['avatar' => 'avatars/existing.png'],
        ]);
        $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
        Socialite::fake('google', $socialUser);
        get('/login/google/callback');

        // When / Then
        Notification::assertSentTo($user, ConfirmSocialLogin::class, function (ConfirmSocialLogin $notification) {
            $response = get($notification->confirmUrl);
            $response->assertRedirect();
            assertAuthenticated();

            return true;
        });
    });

    it('does not require reconfirmation once a provider has been linked', function (): void {
        // Given
        $user = createUser([
            'email' => 'writer@example.com',
            'extra_info' => ['avatar' => 'avatars/existing.png', 'linked_providers' => ['google']],
        ]);
        $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        assertAuthenticated();
    });

    it('does not crash when the provider avatar cannot be fetched', function (): void {
        // Given
        $socialUser = SocialiteUser::fake([
            'email' => 'new-writer@example.com',
            'avatar' => 'https://avatars.example.invalid/does-not-exist.png',
        ]);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        $user = User::where('email', 'new-writer@example.com')->firstOrFail();
        expect($user->extra_info['avatar'] ?? null)->toBeNull();
    });
});
