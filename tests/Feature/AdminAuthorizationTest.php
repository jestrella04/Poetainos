<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guests are redirected away from the admin area', function (): void {
    get('/admin')->assertRedirect(route('login'));
});

test('authenticated non-admins are redirected to login from the admin area', function (): void {
    $user = User::factory()->create();

    actingAs($user)->get('/admin')->assertRedirect(route('login'));
});

test('admins can access the admin area', function (): void {
    $admin = actingAsAdmin();

    actingAs($admin)->get('/admin')->assertOk();
});

test('the tools page exposes structured server info and a log tail', function (): void {
    $admin = actingAsAdmin();

    actingAs($admin)->get(route('admin.tools'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('info')
            ->where('info.PHP version', PHP_VERSION)
            ->has('log'));
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

test('isAllowed returns false when the role has permissions but none match the requested task', function (): void {
    $role = Role::factory()->create([
        'extra_info' => ['permissions' => [['name' => 'moderate', 'enabled' => true]]],
    ]);
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->isAllowed('admin'))->toBeFalse();
});
