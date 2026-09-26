<?php

use App\Models\User;
use App\Models\Writing;
use Illuminate\Support\Facades\DB;

/**
 * The migration that copies users.extra_info and writings.extra_info into columns.
 */
const EXTRA_INFO_MIGRATION = 'database/migrations/2026_09_26_174300_normalize_users_and_writings_extra_info.php';

describe('copying extra_info into columns', function (): void {
    it('moves every profile, account and writing value to its new home', function (): void {
        // Given
        $user = createUser();
        $untouchedUser = createUser();
        $writing = Writing::factory()->for($user, 'author')->create(['cover' => null]);
        pendingArtisan('migrate:rollback', ['--path' => EXTRA_INFO_MIGRATION])->assertSuccessful();
        DB::table('users')->where('id', $user->id)->update(['extra_info' => json_encode([
            'avatar' => 'avatars/avatar.png',
            'bio' => 'A bio',
            'website' => '',
            'social' => ['twitter' => 'tw_handle', 'instagram' => ''],
            'agreement' => ['terms_of_use' => 'on', 'privacy_policy' => 'on'],
            'notifications' => ['email' => 'off'],
            'linked_providers' => ['google'],
        ])]);
        DB::table('writings')->where('id', $writing->id)->update(['extra_info' => json_encode([
            'cover' => 'covers/cover.jpg',
            'link' => '',
        ])]);

        // When
        pendingArtisan('migrate', ['--path' => EXTRA_INFO_MIGRATION])->assertSuccessful();

        // Then
        $user = User::findOrFail($user->id);
        expect($user->profile->only(['avatar', 'bio', 'website', 'twitter', 'instagram']))->toBe([
            'avatar' => 'avatars/avatar.png',
            'bio' => 'A bio',
            'website' => null,
            'twitter' => 'tw_handle',
            'instagram' => null,
        ]);
        expect($user->isInAgreement())->toBeTrue();
        expect($user->wantsEmailNotifications())->toBeFalse();
        expect($user->socialAccounts()->pluck('provider')->all())->toBe(['google']);

        $untouchedUser = User::findOrFail($untouchedUser->id);
        expect($untouchedUser->profile->exists)->toBeFalse();
        expect($untouchedUser->isInAgreement())->toBeFalse();
        expect($untouchedUser->wantsEmailNotifications())->toBeTrue();

        $writing = Writing::findOrFail($writing->id);
        expect($writing->cover)->toBe('covers/cover.jpg');
        expect($writing->link)->toBeNull();
    });
});
