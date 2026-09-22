<?php

use App\Models\Role;
use App\Models\User;
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
        $updateResponse->assertOk();
        $user->refresh();
        expect($user->name)->toBe($name);
        expect($user->extra_info['bio'] ?? null)->toBe($bio);
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
        $response->assertOk();
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
        $response->assertOk();
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
        $updateResponse->assertOk();
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
        $response->assertOk();
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
        $response->assertOk();
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
        $response->assertOk();
        expect(User::find($user->id))->toBeNull();
        assertAuthenticatedAs($admin);
    });

    it('deletes the account, logs the user out and clears their avatar once they confirm their password', function (): void {
        // Given
        Storage::fake('local');
        $avatar = 'avatars/'.fake()->uuid().'.png';
        Storage::disk('local')->put($avatar, fake()->sentence());
        $user = createUser(['extra_info' => ['avatar' => $avatar]]);

        // When
        $response = actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete('/users/delete/'.$user->username);

        // Then
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('message', 'accounts.account-deleted');
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
        $response->assertOk();
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
        $response->assertOk();
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
        $user = createUser([
            'extra_info' => [
                'bio' => fake()->sentence(),
                'notifications' => ['email' => 'off'],
                'linked_providers' => ['google'],
                'agreement' => ['terms_of_use' => 'on', 'privacy_policy' => 'on'],
            ],
        ]);

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
            'bio' => $newBio,
        ]);

        // Then
        $response->assertOk();
        $info = $user->refresh()->extra_info;
        expect($info['bio'])->toBe($newBio);
        expect($info['notifications']['email'])->toBe('off');
        expect($info['linked_providers'])->toBe(['google']);
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
        $response->assertOk();
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
        $user = createUser(['extra_info' => ['avatar' => $oldAvatar, 'bio' => fake()->sentence()]]);

        // When
        $response = actingAs($user)->post('/users/edit/'.$user->username, [
            '_method' => 'PUT',
            'name' => fake()->name(),
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image(fake()->word().'.png', fake()->numberBetween(600, 1200), fake()->numberBetween(600, 1200)),
        ]);

        // Then
        $response->assertOk();
        $avatar = $user->refresh()->extra_info['avatar'];
        expect($avatar)->toStartWith('avatars/')->not->toBe($oldAvatar);
        Storage::disk('local')->assertExists($avatar);
        Storage::disk('local')->assertMissing($oldAvatar);
        expect(getimagesize(Storage::disk('local')->path($avatar))[0])->toBe(512);
    });

    it('is removed on request', function (): void {
        // Given
        $oldAvatar = 'avatars/'.fake()->uuid().'.png';
        Storage::disk('local')->put($oldAvatar, fake()->sentence());
        $user = createUser(['extra_info' => ['avatar' => $oldAvatar]]);

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => fake()->name(),
            'email' => $user->email,
            'avatar-remove' => 1,
        ]);

        // Then
        $response->assertOk();
        expect($user->refresh()->extra_info['avatar'])->toBe('');
        Storage::disk('local')->assertMissing($oldAvatar);
    });
});

describe('the email notification preference', function (): void {
    it('defaults to on and follows what the user chose', function (bool $expected, ?array $info): void {
        // Given
        $user = createUser(['extra_info' => $info]);

        // Then
        expect($user->wantsEmailNotifications())->toBe($expected);
    })->with([
        'never chosen' => [true, null],
        'chose on' => [true, ['notifications' => ['email' => 'on']]],
        'chose off' => [false, ['notifications' => ['email' => 'off']]],
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
