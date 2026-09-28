<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

/**
 * The migration that moves roles.extra_info permissions into permission_role.
 */
const ROLE_PERMISSIONS_MIGRATION = 'database/migrations/2026_09_28_173127_move_role_permissions_out_of_extra_info.php';

describe('moving role permissions out of extra_info', function (): void {
    it('grants each role the permissions its JSON enabled, and nothing else', function (): void {
        // Given
        $admin = Role::factory()->create();
        $restricted = Role::factory()->create();
        $empty = Role::factory()->create();
        pendingArtisan('migrate:rollback', ['--path' => ROLE_PERMISSIONS_MIGRATION])->assertSuccessful();
        DB::table('roles')->where('id', $admin->id)->update(['extra_info' => json_encode(['permissions' => [
            ['name' => 'admin', 'enabled' => true],
            ['name' => 'update_writing', 'enabled' => true],
        ]])]);
        DB::table('roles')->where('id', $restricted->id)->update(['extra_info' => json_encode(['permissions' => [
            ['name' => 'admin', 'enabled' => false],
            ['name' => 'a_custom_permission', 'enabled' => true],
        ]])]);

        // When
        pendingArtisan('migrate', ['--path' => ROLE_PERMISSIONS_MIGRATION])->assertSuccessful();

        // Then
        expect($admin->permissions()->orderBy('name')->pluck('name')->all())->toBe(['admin', 'update_writing']);
        expect($restricted->permissions()->pluck('name')->all())->toBe(['a_custom_permission']);
        expect($empty->permissions()->count())->toBe(0);
        expect(Permission::where('name', 'update_settings')->exists())->toBeTrue();
    });

    it('restores the JSON when rolled back', function (): void {
        // Given
        $role = Role::factory()->admin()->create();

        // When
        pendingArtisan('migrate:rollback', ['--path' => ROLE_PERMISSIONS_MIGRATION])->assertSuccessful();

        // Then
        expect(json_decode((string) DB::table('roles')->where('id', $role->id)->value('extra_info'), true))
            ->toBe(['permissions' => [['name' => 'admin', 'enabled' => true]]]);
        expect(DB::table('permission_role')->count())->toBe(0);
    });
});
