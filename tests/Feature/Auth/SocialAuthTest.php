<?php

use App\Models\User;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\get;

test('social login verifies the email once and does not re-verify on a later login', function (): void {
    $verifiedAt = Carbon::parse('2020-01-01 00:00:00');
    $user = User::factory()->create([
        'email' => 'writer@example.com',
        // Already has an avatar so the callback's avatar-download branch is
        // skipped — this test targets the email-verification branch only.
        'extra_info' => ['avatar' => 'avatars/existing.png'],
        'email_verified_at' => $verifiedAt,
    ]);

    $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
    Socialite::fake('google', $socialUser);

    get('/login/google/callback')->assertRedirect();

    expect($user->refresh()->email_verified_at?->equalTo($verifiedAt) ?? false)->toBeTrue();
});

test('social login verifies a not-yet-verified email on first login', function (): void {
    $user = User::factory()->create([
        'email' => 'writer@example.com',
        'extra_info' => ['avatar' => 'avatars/existing.png'],
        'email_verified_at' => null,
    ]);

    $socialUser = SocialiteUser::fake(['email' => 'writer@example.com']);
    Socialite::fake('google', $socialUser);

    get('/login/google/callback')->assertRedirect();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('social login does not crash when the provider avatar cannot be fetched', function (): void {
    $socialUser = SocialiteUser::fake([
        'email' => 'new-writer@example.com',
        'avatar' => 'https://avatars.example.invalid/does-not-exist.png',
    ]);
    Socialite::fake('google', $socialUser);

    get('/login/google/callback')->assertRedirect();

    $user = User::where('email', 'new-writer@example.com')->firstOrFail();
    expect($user->extra_info['avatar'] ?? null)->toBeNull();
});
