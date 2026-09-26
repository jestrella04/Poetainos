<?php

use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;

describe('viewing and updating a profile', function (): void {
    it('allows a user to view and update their own profile', function (): void {
        // Given
        $user = createUser();
        $name = fake()->name();
        $bio = fake()->sentence();

        // When
        $viewResponse = actingAs($user)->get('/users/edit/'.$user->username);
        $updateResponse = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'bio' => $bio,
        ]);

        // Then
        $viewResponse->assertOk();
        $updateResponse->assertRedirect($user->path())->assertInertiaFlash('message', 'accounts.profile-updated');
        $user->refresh();
        expect($user->name)->toBe($name);
        expect($user->profile->bio)->toBe($bio);
    });

    it('requires re-verification when a user changes their email address', function (): void {
        // Given
        $user = createUser(['email_verified_at' => now()]);

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ]);

        // Then
        $response->assertRedirect();
        expect($user->refresh()->email_verified_at)->toBeNull();
    });

    it('keeps the account verified when the email is left unchanged', function (): void {
        // Given
        $user = createUser(['email_verified_at' => now()]);

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
        ]);

        // Then
        $response->assertRedirect();
        expect($user->refresh()->email_verified_at)->not->toBeNull();
    });

    it('forbids a different verified user from viewing or updating someone else\'s profile', function (): void {
        // Given
        $user = createUser();
        $other = createUser();

        // When
        $viewResponse = actingAs($other)->get('/users/edit/'.$user->username);
        $updateResponse = actingAs($other)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ]);

        // Then
        $viewResponse->assertForbidden();
        $updateResponse->assertForbidden();
    });

    it('allows an admin to view and update any profile', function (): void {
        // Given
        $user = createUser();
        $admin = actingAsAdmin();
        $name = fake()->name();

        // When
        $viewResponse = actingAs($admin)->get('/users/edit/'.$user->username);
        $updateResponse = actingAs($admin)->put('/users/edit/'.$user->username, [
            'name' => $name,
            'email' => $user->email,
        ]);

        // Then
        $viewResponse->assertOk();
        $updateResponse->assertRedirect();
        expect($user->refresh()->name)->toBe($name);
    });
});

describe('changing a user\'s role', function (): void {
    it('prevents a non-admin from changing their own role', function (): void {
        // Given
        $adminRole = Role::factory()->admin()->create();
        $user = createUser();

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
            'role' => $adminRole->id,
        ]);

        // Then
        $response->assertRedirect();
        expect($user->refresh()->role_id)->not->toBe($adminRole->id);
    });

    it('allows an admin to change a user\'s role', function (): void {
        // Given
        $adminRole = Role::factory()->admin()->create();
        $user = createUser();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
            'role' => $adminRole->id,
        ]);

        // Then
        $response->assertRedirect();
        expect($user->refresh()->role_id)->toBe($adminRole->id);
    });
});

describe('deleting an account', function (): void {
    it('allows an admin to delete a different user without confirming a password and stay logged in', function (): void {
        // Given
        $user = createUser();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->delete('/admin/users/delete/'.$user->username);

        // Then
        $response->assertRedirect(route('home'))->assertInertiaFlash('message', 'users.user-deleted');
        expect(User::find($user->id))->toBeNull();
        assertAuthenticatedAs($admin);
    });

    it('deletes the account, logs the user out and clears their avatar once they confirm their password', function (): void {
        // Given
        Storage::fake('local');
        $avatar = 'avatars/'.fake()->uuid().'.png';
        Storage::disk('local')->put($avatar, fake()->sentence());
        $user = createUser();
        UserProfile::factory()->for($user)->create(['avatar' => $avatar]);

        // When
        $response = actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete('/users/delete/'.$user->username);

        // Then
        $response->assertRedirect(route('home'));
        $response->assertInertiaFlash('message', 'accounts.account-deleted');
        expect(User::find($user->id))->toBeNull();
        assertGuest();
        Storage::disk('local')->assertMissing($avatar);
    });
});

describe('blocking a user', function (): void {
    it('allows a user to block another user', function (): void {
        // Given
        $user = createUser();
        $author = createUser();

        // When
        $response = actingAs($user)->post('/users/block/'.$author->username);

        // Then
        $response->assertRedirect()->assertInertiaFlash('message', 'users.user-blocked');
        expect($user->refresh()->isAuthorBlocked($author))->toBeTrue();
    });

    it('does not let a user block themselves', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->post('/users/block/'.$user->username);

        // Then
        $response->assertUnprocessable();
        expect($user->blockedAuthors()->count())->toBe(0);
    });

    it('lets a user unblock someone they blocked', function (): void {
        // Given
        $user = createUser();
        $author = createUser();
        $user->block($author);

        // When
        $response = actingAs($user)->delete('/users/block/'.$author->username);

        // Then
        $response->assertRedirect()->assertInertiaFlash('message', 'users.user-unblocked');
        expect($user->isAuthorBlocked($author))->toBeFalse();
    });
});

describe('what a profile update keeps and rejects', function (): void {
    beforeEach(function (): void {
        config(['inertia.testing.ensure_pages_exist' => false]);
    });

    it('keeps the stored settings the form does not own', function (): void {
        // Given
        $newBio = fake()->sentence();
        $avatar = 'avatars/'.fake()->uuid().'.png';
        $user = createUser([
            'wants_email_notifications' => false,
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
        ]);
        UserProfile::factory()->for($user)->create(['bio' => fake()->sentence(), 'avatar' => $avatar]);
        SocialAccount::factory()->for($user)->create(['provider' => 'google']);

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
            'bio' => $newBio,
        ]);

        // Then
        $response->assertRedirect();
        $user->refresh();
        expect($user->profile->bio)->toBe($newBio);
        expect($user->profile->avatar)->toBe($avatar);
        expect($user->wantsEmailNotifications())->toBeFalse();
        expect($user->socialAccounts()->pluck('provider')->all())->toBe(['google']);
        expect($user->isInAgreement())->toBeTrue();
    });

    it('rejects an email that another account already uses', function (): void {
        // Given
        $taken = createUser();
        $user = createUser();

        // When
        $response = actingAs($user)->putJson('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $taken->email,
        ]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    });

    it('lets a user keep their own email', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->putJson('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
        ]);

        // Then
        $response->assertRedirect();
    });

    it('only offers the role list to admins', function (): void {
        // Given
        $user = createUser();
        $admin = actingAsAdmin();

        // When
        $asUser = actingAs($user)->get('/users/edit/'.$user->username);
        $asAdmin = actingAs($admin)->get('/users/edit/'.$user->username);

        // Then
        $asUser->assertInertia(fn ($page) => $page->has('roles', 0));
        $asAdmin->assertInertia(fn ($page) => $page->has('roles', Role::count()));
    });
});

describe('a profile avatar', function (): void {
    beforeEach(function (): void {
        Storage::fake('local');
    });

    it('is stored, replacing and deleting the previous file', function (): void {
        // Given
        $oldAvatar = 'avatars/'.fake()->uuid().'.png';
        Storage::disk('local')->put($oldAvatar, fake()->sentence());
        $user = createUser();
        UserProfile::factory()->for($user)->create(['avatar' => $oldAvatar, 'bio' => fake()->sentence()]);

        // When
        $response = actingAs($user)->post('/users/edit/'.$user->username, [
            '_method' => 'PUT',
            'name' => fake()->name(),
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image(fake()->word().'.png', fake()->numberBetween(600, 1200), fake()->numberBetween(600, 1200)),
        ]);

        // Then
        $response->assertRedirect();
        $avatar = (string) $user->refresh()->profile->avatar;
        expect($avatar)->toStartWith('avatars/')->not->toBe($oldAvatar);
        Storage::disk('local')->assertExists($avatar);
        Storage::disk('local')->assertMissing($oldAvatar);
        expect(storedImageWidth($avatar))->toBe(512);
    });

    it('is removed on request', function (): void {
        // Given
        $oldAvatar = 'avatars/'.fake()->uuid().'.png';
        Storage::disk('local')->put($oldAvatar, fake()->sentence());
        $user = createUser();
        UserProfile::factory()->for($user)->create(['avatar' => $oldAvatar]);

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
            'avatar-remove' => 1,
        ]);

        // Then
        $response->assertRedirect();
        expect($user->refresh()->profile->avatar)->toBeNull();
        Storage::disk('local')->assertMissing($oldAvatar);
    });
});

describe('the email notification preference', function (): void {
    it('defaults to on and follows what the user chose', function (bool $expected, ?bool $choice): void {
        // Given
        $user = createUser($choice === null ? [] : ['wants_email_notifications' => $choice]);

        // Then
        expect($user->refresh()->wantsEmailNotifications())->toBe($expected);
    })->with([
        'never chosen' => [true, null],
        'chose on' => [true, true],
        'chose off' => [false, false],
    ]);

    it('is switched through the notifications endpoint', function (): void {
        // Given
        $user = createUser();

        // When
        actingAs($user)->post(route('notifications.email', ['false']))->assertNoContent();

        // Then
        expect($user->refresh()->wantsEmailNotifications())->toBeFalse();
    });
});
