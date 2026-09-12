<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('viewing and updating a profile', function (): void {
    it('allows a user to view and update their own profile', function (): void {
        // Given
        $user = createUser();

        // When
        $viewResponse = actingAs($user)->get('/users/edit/'.$user->username);
        $updateResponse = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'bio' => 'A short bio.',
        ]);

        // Then
        $viewResponse->assertOk();
        $updateResponse->assertOk();
        $user->refresh();
        expect($user->name)->toBe('Updated Name');
        expect($user->extra_info['bio'] ?? null)->toBe('A short bio.');
    });

    it('requires re-verification when a user changes their email address', function (): void {
        // Given
        $user = createUser(['email_verified_at' => now()]);

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => 'Updated Name',
            'email' => 'new-address@example.com',
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
            'name' => 'Updated Name',
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
            'name' => 'Someone else',
            'email' => 'someone-else@example.com',
        ]);

        // Then
        $viewResponse->assertForbidden();
        $updateResponse->assertForbidden();
    });

    it('allows an admin to view and update any profile', function (): void {
        // Given
        $user = createUser();
        $admin = actingAsAdmin();

        // When
        $viewResponse = actingAs($admin)->get('/users/edit/'.$user->username);
        $updateResponse = actingAs($admin)->put('/users/edit/'.$user->username, [
            'name' => 'Admin Edited',
            'email' => $user->email,
        ]);

        // Then
        $viewResponse->assertOk();
        $updateResponse->assertOk();
        expect($user->refresh()->name)->toBe('Admin Edited');
    });
});

describe('changing a user\'s role', function (): void {
    it('prevents a non-admin from changing their own role', function (): void {
        // Given
        $adminRole = Role::factory()->admin()->create();
        $user = createUser();

        // When
        $response = actingAs($user)->put('/users/edit/'.$user->username, [
            'name' => 'Just a user',
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
            'name' => 'Just a user',
            'email' => $user->email,
            'role' => $adminRole->id,
        ]);

        // Then
        $response->assertOk();
        expect($user->refresh()->role_id)->toBe($adminRole->id);
    });
});

describe('deleting an account', function (): void {
    it('allows a user to delete their own account after confirming their password', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete('/users/delete/'.$user->username);

        // Then
        $response->assertRedirect(route('home'));
        expect(User::find($user->id))->toBeNull();
    });

    it('allows an admin to delete a different user without confirming a password', function (): void {
        // Given
        $user = createUser();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->delete('/admin/users/delete/'.$user->username);

        // Then
        $response->assertOk();
        expect(User::find($user->id))->toBeNull();
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
});
