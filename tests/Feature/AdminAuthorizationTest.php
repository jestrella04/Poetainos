<?php

use App\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('the admin area', function (): void {
    it('redirects guests away', function (): void {
        // When
        $response = get('/admin');

        // Then
        $response->assertRedirect(route('login'));
    });

    it('forbids authenticated non-admins', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->get('/admin');

        // Then
        $response->assertForbidden();
    });

    it('is accessible to admins', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->get('/admin');

        // Then
        $response->assertOk();
    });
});

describe('the shared auth props', function (): void {
    it('flag admins so the admin menu entry is rendered', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->get('/admin');

        // Then
        $response->assertInertia(fn ($page) => $page->where('auth.admin', true));
    });

    it('do not flag regular users as admins', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->get(route('users.index'));

        // Then
        $response->assertInertia(fn ($page) => $page->where('auth.admin', false));
    });
});

describe('the tools page', function (): void {
    it('exposes structured server info and a log tail', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->get(route('admin.tools'));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('info')
                ->where('info.PHP version', PHP_VERSION)
                ->has('log'));
    });
});

describe('isAllowed', function (): void {
    it('reflects the role\'s admin permission', function (): void {
        // Given
        $noRole = createUser();

        // Then
        expect($noRole->isAllowed('admin'))->toBeFalse();

        // Given
        $plainRole = Role::factory()->create();
        $plainRoleUser = createUser(['role_id' => $plainRole->id]);

        // Then
        expect($plainRoleUser->isAllowed('admin'))->toBeFalse();

        // Given
        $disabledAdminRole = Role::factory()->create([
            'extra_info' => ['permissions' => [['name' => 'admin', 'enabled' => false]]],
        ]);
        $disabledAdminUser = createUser(['role_id' => $disabledAdminRole->id]);

        // Then
        expect($disabledAdminUser->isAllowed('admin'))->toBeFalse();

        // Given
        $admin = actingAsAdmin();

        // Then
        expect($admin->isAllowed('admin'))->toBeTrue();
    });

    it('returns false when the role has permissions but none match the requested task', function (): void {
        // Given
        $role = Role::factory()->create([
            'extra_info' => ['permissions' => [['name' => 'moderate', 'enabled' => true]]],
        ]);
        $user = createUser(['role_id' => $role->id]);

        // Then
        expect($user->isAllowed('admin'))->toBeFalse();
    });
});
