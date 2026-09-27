<?php

use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\SocialLoginCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

function fakeAvatarPath(): string
{
    return 'avatars/'.fake()->uuid().'.png';
}

/**
 * A user with the given profile fields and social login providers already linked.
 *
 * @param  array<string, mixed>  $attributes
 * @param  array<model-property<UserProfile>, mixed>  $profile
 * @param  list<string>  $linkedProviders
 */
function createSocialUser(array $attributes = [], array $profile = [], array $linkedProviders = []): User
{
    $user = createUser($attributes);

    if ($profile !== []) {
        UserProfile::factory()->for($user)->create(['avatar' => null, 'bio' => null, ...$profile]);
    }

    foreach ($linkedProviders as $provider) {
        SocialAccount::factory()->for($user)->create(['provider' => $provider]);
    }

    return $user;
}

/**
 * Sign in with Google as an existing account that has never used it, and
 * return the confirmation code emailed to the account.
 */
function startProviderLink(User $user, ?string $avatarUrl = null): string
{
    Notification::fake();
    Socialite::fake('google', SocialiteUser::fake(['email' => $user->email, 'avatar' => $avatarUrl]));
    get('/login/google/callback');

    return sentLinkCode($user);
}

function sentLinkCode(User $user): string
{
    $code = '';
    Notification::assertSentTo($user, SocialLoginCode::class, function (SocialLoginCode $notification) use (&$code): bool {
        $code = $notification->code;

        return true;
    });

    return $code;
}

describe('social login', function (): void {
    it('verifies the email once and does not re-verify on a later login', function (): void {
        // Given
        $verifiedAt = Carbon::instance(fake()->dateTimeBetween('-5 years', '-1 day'));
        $email = fake()->unique()->safeEmail();
        // Already has an avatar and a linked Google provider, so the callback's
        // avatar-download and confirm-a-new-provider branches are both skipped —
        // this test targets the email-verification branch only.
        $user = createSocialUser(['email' => $email, 'email_verified_at' => $verifiedAt], ['avatar' => fakeAvatarPath()], ['google']);
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
        $user = createSocialUser(['email' => $email, 'email_verified_at' => null], ['avatar' => fakeAvatarPath()], ['google']);
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
        $user = createSocialUser(['email' => $email], ['avatar' => fakeAvatarPath()], ['google']);
        $socialUser = SocialiteUser::fake(['email' => $email]);
        Socialite::fake('google', $socialUser);

        // When
        get('/login/google?redirect=https://evil.example/phish');
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect(route('home'));
    });

    it('asks an existing account for an emailed code before it trusts a new provider', function (): void {
        // Given
        Notification::fake();
        $user = createSocialUser([], ['avatar' => fakeAvatarPath()]);
        Socialite::fake('google', SocialiteUser::fake(['email' => $user->email]));

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect(route('social.confirm', 'google'));
        assertGuest();
        Notification::assertSentTo($user, SocialLoginCode::class);
    });

    it('shows the code confirmation page while a sign-in awaits confirmation', function (): void {
        // Given
        $user = createSocialUser([], ['avatar' => fakeAvatarPath()]);
        startProviderLink($user);

        // When
        $response = get(route('social.confirm', 'google'));

        // Then
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/PoSocialConfirm', false)
            ->where('service', 'google')
            ->where('email', $user->email));
    });

    it('sends the user back to the login page when no sign-in awaits confirmation', function (): void {
        // When
        $response = get(route('social.confirm', 'google'));

        // Then
        $response->assertRedirect(route('login'));
        $response->assertInertiaFlash('message', 'accounts.social-link-expired');
    });

    it('logs the user in and links the provider once the emailed code is entered', function (): void {
        // Given
        $user = createSocialUser(['email_verified_at' => null], ['avatar' => fakeAvatarPath()]);
        $code = startProviderLink($user);

        // When
        $response = postJson(route('social.confirm.verify', 'google'), ['code' => $code]);

        // Then
        $response->assertRedirect(route('home'));
        assertAuthenticatedAs($user);
        $user->refresh();
        expect($user->socialAccounts()->pluck('provider')->all())->toBe(['google']);
        expect($user->email_verified_at)->not->toBeNull();
    });

    it('returns to the page the user started from once the code is entered', function (): void {
        // Given
        $user = createSocialUser([], ['avatar' => fakeAvatarPath()]);
        $intendedPath = '/'.fake()->slug();
        get(route('social.login', ['service' => 'google', 'redirect' => $intendedPath]));
        $code = startProviderLink($user);

        // When
        $response = postJson(route('social.confirm.verify', 'google'), ['code' => $code]);

        // Then
        $response->assertRedirect(url($intendedPath));
    });

    it('imports the provider avatar once the code is entered', function (): void {
        // Given
        Storage::fake('local');
        ob_start();
        imagepng(imagecreatetruecolor(600, 600));
        $png = (string) ob_get_clean();
        Http::fake(['avatars.example/*' => Http::response($png)]);
        $user = createUser();
        $code = startProviderLink($user, 'https://avatars.example/'.fake()->uuid().'.png');

        // When
        postJson(route('social.confirm.verify', 'google'), ['code' => $code])->assertRedirect();

        // Then
        $avatar = (string) $user->refresh()->profile->avatar;
        expect($avatar)->toStartWith('avatars/');
        Storage::disk('local')->assertExists($avatar);
    });

    it('rejects a wrong code and keeps the user signed out', function (): void {
        // Given
        $user = createSocialUser([], ['avatar' => fakeAvatarPath()]);
        $code = startProviderLink($user);

        // When
        $response = postJson(route('social.confirm.verify', 'google'), ['code' => wrongCodeFor($code)]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'code' => __('The verification code is invalid or has expired.'),
        ]);
        assertGuest();
        expect($user->socialAccounts()->exists())->toBeFalse();
    });

    it('rejects a code when no sign-in awaits confirmation', function (): void {
        // When
        $response = postJson(route('social.confirm.verify', 'google'), ['code' => fake()->numerify('######')]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'code' => __('Your sign-in request has expired. Please sign in again.'),
        ]);
        assertGuest();
    });

    it('emails a fresh code on request and stops accepting the previous one', function (): void {
        // Given
        $user = createSocialUser([], ['avatar' => fakeAvatarPath()]);
        $firstCode = startProviderLink($user);
        Notification::fake();

        // When
        $response = post(route('social.confirm.resend', 'google'));

        // Then
        $response->assertNoContent();
        $secondCode = sentLinkCode($user);
        postJson(route('social.confirm.verify', 'google'), ['code' => $firstCode])->assertUnprocessable();
        postJson(route('social.confirm.verify', 'google'), ['code' => $secondCode])->assertRedirect();
        assertAuthenticatedAs($user);
    });

    it('does not require reconfirmation once a provider has been linked', function (): void {
        // Given
        $email = fake()->unique()->safeEmail();
        $user = createSocialUser(['email' => $email], ['avatar' => fakeAvatarPath()], ['google']);
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
        expect($user->profile->avatar)->toBeNull();
    });

    it('refuses a login when the provider shares no email address', function (): void {
        // Given
        Socialite::fake('google', SocialiteUser::fake(['email' => null]));

        // When
        $response = get('/login/google/callback');

        // Then
        $response->assertRedirect(route('login'));
        $response->assertInertiaFlash('message', 'accounts.social-email-missing');
        assertGuest();
        expect(User::count())->toBe(0);
    });

    it('sends the user back to the login page when the callback state does not match the session', function (): void {
        // Given
        config(['services.google.client_id' => 'client-id', 'services.google.client_secret' => 'client-secret']);

        // When
        $response = get('/login/google/callback?state='.fake()->sha1().'&code='.fake()->sha1());

        // Then
        $response->assertRedirect(route('login'));
        $response->assertInertiaFlash('message', 'accounts.social-link-expired');
        assertGuest();
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
        $user = createSocialUser(['email' => $email, 'wants_email_notifications' => false], ['bio' => $bio], ['google']);
        Socialite::fake('google', SocialiteUser::fake([
            'email' => $email,
            'avatar' => 'https://avatars.example/'.fake()->uuid().'.png',
        ]));

        // When
        get('/login/google/callback')->assertRedirect();

        // Then
        $user->refresh();
        expect($user->profile->bio)->toBe($bio);
        expect($user->wantsEmailNotifications())->toBeFalse();
        $avatar = (string) $user->profile->avatar;
        expect($avatar)->toStartWith('avatars/')->toEndWith('.png');
        Storage::disk('local')->assertExists($avatar);
        expect(storedImageWidth($avatar))->toBe(512);
    });

    it('ignores a provider avatar that is not a supported image', function (string $body): void {
        // Given
        Storage::fake('local');
        Http::fake(['avatars.example/*' => Http::response($body)]);
        $bio = fake()->sentence();
        $email = fake()->unique()->safeEmail();
        $user = createSocialUser(['email' => $email], ['bio' => $bio], ['google']);
        Socialite::fake('google', SocialiteUser::fake([
            'email' => $email,
            'avatar' => 'https://avatars.example/'.fake()->uuid().'.png',
        ]));

        // When
        get('/login/google/callback')->assertRedirect();

        // Then
        expect($user->refresh()->profile->avatar)->toBeNull();
        expect($user->profile->bio)->toBe($bio);
        expect(Storage::disk('local')->allFiles())->toBe([]);
    })->with([
        'a web page' => [fn (): string => '<html>'.fake()->sentence().'</html>'],
        'a gif' => ['GIF89a'.str_repeat("\0", 32)],
    ]);

    it('rejects a provider that is not offered', function (string $path): void {
        // When
        $response = get($path);

        // Then
        $response->assertNotFound();
    })->with([
        'redirect' => ['/login/facebook'],
        'callback' => ['/login/facebook/callback'],
    ]);
});
