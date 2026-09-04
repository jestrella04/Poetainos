<?php

use App\Models\Role;
use App\Models\User;

test('guests are redirected away from the admin area', function (): void {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('authenticated non-admins are redirected to login from the admin area', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertRedirect(route('login'));
});

test('admins can access the admin area', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('isAllowed reflects the role\'s admin permission', function (): void {
    $noRole = User::factory()->create();
    expect($noRole->isAllowed('admin'))->toBeFalse();

    $plainRole = Role::factory()->create();
    $plainRoleUser = User::factory()->create(['role_id' => $plainRole->id]);
    expect($plainRoleUser->isAllowed('admin'))->toBeFalse();

    $disabledAdminRole = Role::factory()->create([
        'extra_info' => ['permissions' => [['name' => 'admin', 'enabled' => false]]],
    ]);
    $disabledAdminUser = User::factory()->create(['role_id' => $disabledAdminRole->id]);
    expect($disabledAdminUser->isAllowed('admin'))->toBeFalse();

    $admin = actingAsAdmin();
    expect($admin->isAllowed('admin'))->toBeTrue();
});
