<?php

use App\Models\User;
use App\Notifications\ConfirmSocialLogin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

function fakeAvatarPath(): string
{
    return 'avatars/'.fake()->uuid().'.png';
}

describe('social login', function (): void {
    it('verifies the email once and does not re-verify on a later login', function (): void {
        // Given
        $verifiedAt = Carbon::instance(fake()->dateTimeBetween('-5 years', '-1 day'));
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            // Already has an avatar and a linked Google provider, so the callback's
            // avatar-download and confirm-a-new-provider branches are both skipped —
            // this test targets the email-verification branch only.
            'extra_info' => ['avatar' => fakeAvatarPath(), 'linked_providers' => ['google']],
            'email_verified_at' => $verifiedAt,
        ]);
        $socialUser = SocialiteUser::fake(['email' => $email]);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        expect($user->refresh()->email_verified_at?->equalTo($verifiedAt) ?? false)->toBeTrue();
    });

    it('verifies a not-yet-verified email on first login', function (): void {
        // Given
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            'extra_info' => ['avatar' => fakeAvatarPath(), 'linked_providers' => ['google']],
            'email_verified_at' => null,
        ]);
        $socialUser = SocialiteUser::fake(['email' => $email]);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        expect($user->refresh()->email_verified_at)->not->toBeNull();
    });

    it('ignores an external redirect target to prevent an open redirect', function (): void {
        // Given
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            'extra_info' => ['avatar' => fakeAvatarPath(), 'linked_providers' => ['google']],
        ]);
        $socialUser = SocialiteUser::fake(['email' => $email]);
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
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            'extra_info' => ['avatar' => fakeAvatarPath()],
        ]);
        $socialUser = SocialiteUser::fake(['email' => $email]);
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
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            'extra_info' => ['avatar' => fakeAvatarPath()],
        ]);
        $socialUser = SocialiteUser::fake(['email' => $email]);
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
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            'extra_info' => ['avatar' => fakeAvatarPath(), 'linked_providers' => ['google']],
        ]);
        $socialUser = SocialiteUser::fake(['email' => $email]);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        assertAuthenticated();
    });

    it('does not crash when the provider avatar cannot be fetched', function (): void {
        // Given
        $email = fake()->unique()->safeEmail();
        $socialUser = SocialiteUser::fake([
            'email' => $email,
            'avatar' => 'https://avatars.example.invalid/'.fake()->uuid().'.png',
        ]);
        Socialite::fake('google', $socialUser);

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect();
        $user = User::where('email', $email)->firstOrFail();
        expect($user->extra_info['avatar'] ?? null)->toBeNull();
    });

    it('refuses a login when the provider shares no email address', function (): void {
        // Given
        Socialite::fake('twitter', SocialiteUser::fake(['email' => null]));

        // When
        $response = get('/login/twitter/callback');

        // Then
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('message', 'accounts.social-email-missing');
        assertGuest();
        expect(User::count())->toBe(0);
    });

    it('keeps the rest of the profile when it imports the provider avatar', function (): void {
        // Given
        Storage::fake('local');
        ob_start();
        imagepng(imagecreatetruecolor(800, 600));
        $png = (string) ob_get_clean();
        Http::fake(['avatars.example/*' => Http::response($png)]);
        $bio = fake()->sentence();
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            'extra_info' => [
                'bio' => $bio,
                'avatar' => '',
                'notifications' => ['email' => 'off'],
                'linked_providers' => ['google'],
            ],
        ]);
        Socialite::fake('google', SocialiteUser::fake([
            'email' => $email,
            'avatar' => 'https://avatars.example/'.fake()->uuid().'.png',
        ]));

        // When
        get('/login/google/callback')->assertRedirect();

        // Then
        $info = $user->refresh()->extra_info;
        expect($info['bio'])->toBe($bio);
        expect($info['notifications']['email'])->toBe('off');
        expect($info['avatar'])->toStartWith('avatars/')->toEndWith('.png');
        Storage::disk('local')->assertExists($info['avatar']);
        expect(getimagesize(Storage::disk('local')->path($info['avatar']))[0])->toBe(512);
    });

    it('ignores a provider avatar that is not a supported image', function (string $body): void {
        // Given
        Storage::fake('local');
        Http::fake(['avatars.example/*' => Http::response($body)]);
        $bio = fake()->sentence();
        $email = fake()->unique()->safeEmail();
        $user = createUser([
            'email' => $email,
            'extra_info' => ['bio' => $bio, 'linked_providers' => ['google']],
        ]);
        Socialite::fake('google', SocialiteUser::fake([
            'email' => $email,
            'avatar' => 'https://avatars.example/'.fake()->uuid().'.png',
        ]));

        // When
        get('/login/google/callback')->assertRedirect();

        // Then
        expect($user->refresh()->extra_info['avatar'] ?? null)->toBeNull();
        expect($user->extra_info['bio'])->toBe($bio);
        expect(Storage::disk('local')->allFiles())->toBe([]);
    })->with([
        'a web page' => [fn (): string => '<html>'.fake()->sentence().'</html>'],
        'a gif' => ['GIF89a'.str_repeat("\0", 32)],
    ]);
});
