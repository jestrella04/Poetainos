<?php

use App\Models\Role;
use App\Models\Tag;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    useTemporaryLogs([]);
    config(['logging.channels.security.path' => storage_path('logs/security.log')]);
});

afterEach(function (): void {
    deleteTemporaryLogs();
});

/**
 * Everything written to the security audit log so far.
 */
function securityLogContents(): string
{
    return implode('', array_map(file_get_contents(...), glob(storage_path('logs/security-*.log')) ?: []));
}

describe('the admin audit trail', function (): void {
    it('records who changed what through the admin panel', function (): void {
        // Given
        $admin = actingAsAdmin();
        $tag = Tag::factory()->create();

        // When
        actingAs($admin)->deleteJson(route('admin.tags.destroy', $tag))->assertOk();

        // Then
        expect(securityLogContents())
            ->toContain('Admin action')
            ->toContain('"actor_id":'.$admin->id)
            ->toContain('"action":"admin.tags.destroy"')
            ->toContain('"status":200');
    });

    it('does not record admin pages that are only read', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        actingAs($admin)->getJson(route('admin.tags'))->assertOk();

        // Then
        expect(securityLogContents())->toBe('');
    });

    it('records a change of role', function (): void {
        // Given
        $admin = actingAsAdmin();
        $user = createUser();
        $newRole = Role::factory()->admin()->create();

        // When
        actingAs($admin)->put(route('users.update', $user->username), [
            'name' => fake()->name(),
            'email' => $user->email,
            'role' => $newRole->id,
        ])->assertRedirect();

        // Then
        expect(securityLogContents())
            ->toContain('Role changed')
            ->toContain('"actor_id":'.$admin->id)
            ->toContain('"user_id":'.$user->id)
            ->toContain('"to_role_id":'.$newRole->id);
    });
});
