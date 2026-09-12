<?php

use App\Models\Role;
use App\Models\User;

test('a user can view and update their own profile', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/users/edit/'.$user->username)->assertOk();

    $this->actingAs($user)->put('/users/edit/'.$user->username, [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'bio' => 'A short bio.',
    ])->assertOk();

    $fresh = $user->fresh();
    expect($fresh->name)->toBe('Updated Name');
    expect($fresh->extra_info['bio'])->toBe('A short bio.');
});

test('a different verified user cannot view or update someone else\'s profile', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)->get('/users/edit/'.$user->username)->assertForbidden();
    $this->actingAs($other)->put('/users/edit/'.$user->username, [
        'name' => 'Someone else',
        'email' => 'someone-else@example.com',
    ])->assertForbidden();
});

test('an admin can view and update any profile', function (): void {
    $user = User::factory()->create();
    $admin = actingAsAdmin();

    $this->actingAs($admin)->get('/users/edit/'.$user->username)->assertOk();
    $this->actingAs($admin)->put('/users/edit/'.$user->username, [
        'name' => 'Admin Edited',
        'email' => $user->email,
    ])->assertOk();

    expect($user->fresh()->name)->toBe('Admin Edited');
});

test('a non-admin cannot change their own role', function (): void {
    $adminRole = Role::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->put('/users/edit/'.$user->username, [
        'name' => 'Just a user',
        'email' => $user->email,
        'role' => $adminRole->id,
    ])->assertOk();

    expect($user->fresh()->role_id)->not->toBe($adminRole->id);
});

test('an admin can change a user\'s role', function (): void {
    $adminRole = Role::factory()->admin()->create();
    $user = User::factory()->create();
    $admin = actingAsAdmin();

    $this->actingAs($admin)->put('/users/edit/'.$user->username, [
        'name' => 'Just a user',
        'email' => $user->email,
        'role' => $adminRole->id,
    ])->assertOk();

    expect($user->fresh()->role_id)->toBe($adminRole->id);
});

test('a user can delete their own account after confirming their password', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete('/users/delete/'.$user->username)
        ->assertRedirect(route('home'));

    expect(User::find($user->id))->toBeNull();
});

test('an admin can delete a different user without confirming a password', function (): void {
    $user = User::factory()->create();
    $admin = actingAsAdmin();

    $this->actingAs($admin)->delete('/admin/users/delete/'.$user->username)->assertOk();

    expect(User::find($user->id))->toBeNull();
});

test('a user can block another user', function (): void {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $this->actingAs($user)->post('/users/block/'.$author->username)->assertOk();

    expect($user->fresh()->isAuthorBlocked($author))->toBeTrue();
});
