<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('a user can view and update their own profile', function (): void {
    $user = User::factory()->create();

    actingAs($user)->get('/users/edit/'.$user->username)->assertOk();

    actingAs($user)->put('/users/edit/'.$user->username, [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'bio' => 'A short bio.',
    ])->assertOk();

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->extra_info['bio'] ?? null)->toBe('A short bio.');
});

test('a different verified user cannot view or update someone else\'s profile', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    actingAs($other)->get('/users/edit/'.$user->username)->assertForbidden();
    actingAs($other)->put('/users/edit/'.$user->username, [
        'name' => 'Someone else',
        'email' => 'someone-else@example.com',
    ])->assertForbidden();
});

test('an admin can view and update any profile', function (): void {
    $user = User::factory()->create();
    $admin = actingAsAdmin();

    actingAs($admin)->get('/users/edit/'.$user->username)->assertOk();
    actingAs($admin)->put('/users/edit/'.$user->username, [
        'name' => 'Admin Edited',
        'email' => $user->email,
    ])->assertOk();

    expect($user->refresh()->name)->toBe('Admin Edited');
});

test('a non-admin cannot change their own role', function (): void {
    $adminRole = Role::factory()->admin()->create();
    $user = User::factory()->create();

    actingAs($user)->put('/users/edit/'.$user->username, [
        'name' => 'Just a user',
        'email' => $user->email,
        'role' => $adminRole->id,
    ])->assertOk();

    expect($user->refresh()->role_id)->not->toBe($adminRole->id);
});

test('an admin can change a user\'s role', function (): void {
    $adminRole = Role::factory()->admin()->create();
    $user = User::factory()->create();
    $admin = actingAsAdmin();

    actingAs($admin)->put('/users/edit/'.$user->username, [
        'name' => 'Just a user',
        'email' => $user->email,
        'role' => $adminRole->id,
    ])->assertOk();

    expect($user->refresh()->role_id)->toBe($adminRole->id);
});

test('a user can delete their own account after confirming their password', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete('/users/delete/'.$user->username)
        ->assertRedirect(route('home'));

    expect(User::find($user->id))->toBeNull();
});

test('an admin can delete a different user without confirming a password', function (): void {
    $user = User::factory()->create();
    $admin = actingAsAdmin();

    actingAs($admin)->delete('/admin/users/delete/'.$user->username)->assertOk();

    expect(User::find($user->id))->toBeNull();
});

test('a user can block another user', function (): void {
    $user = User::factory()->create();
    $author = User::factory()->create();

    actingAs($user)->post('/users/block/'.$author->username)->assertOk();

    expect($user->refresh()->isAuthorBlocked($author))->toBeTrue();
});
