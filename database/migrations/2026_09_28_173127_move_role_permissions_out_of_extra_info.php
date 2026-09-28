<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves each role's enabled permissions from the `permissions` list of its
 * roles.extra_info JSON into permission_role rows, then drops extra_info,
 * which held nothing else.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = DB::table('roles')->select('id', 'extra_info')->get();
        $grants = $roles->mapWithKeys(fn (stdClass $role): array => [$role->id => $this->enabledPermissions($role->extra_info)]);

        // Every known permission exists, even one no role holds yet
        $names = collect($this->knownPermissions())->merge($grants->flatten())->unique()->values();
        $now = Carbon::now();

        DB::table('permissions')->insertOrIgnore(
            $names->map(fn (string $name): array => ['name' => $name, 'created_at' => $now, 'updated_at' => $now])->all()
        );

        $permissionIds = DB::table('permissions')->pluck('id', 'name');

        foreach ($grants as $roleId => $roleGrants) {
            DB::table('permission_role')->insertOrIgnore(
                collect($roleGrants)->map(fn (string $name): array => ['permission_id' => $permissionIds[$name], 'role_id' => $roleId])->all()
            );
        }

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('extra_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('extra_info')->nullable()->after('description');
        });

        $grants = DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->select('permission_role.role_id', 'permissions.name')
            ->get()
            ->groupBy('role_id');

        foreach ($grants as $roleId => $roleGrants) {
            DB::table('roles')->where('id', $roleId)->update(['extra_info' => json_encode([
                'permissions' => $roleGrants->map(fn (stdClass $grant): array => ['name' => $grant->name, 'enabled' => true])->values()->all(),
            ])]);
        }

        DB::table('permission_role')->delete();
        DB::table('permissions')->delete();
    }

    /**
     * @return list<string>
     */
    private function enabledPermissions(?string $extraInfo): array
    {
        $extraInfo = json_decode((string) $extraInfo, true);
        $names = [];

        foreach (is_array($extraInfo) && is_array($extraInfo['permissions'] ?? null) ? $extraInfo['permissions'] : [] as $permission) {
            if (is_array($permission) && ($permission['enabled'] ?? false) === true && is_string($permission['name'] ?? null)) {
                $names[] = $permission['name'];
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return list<string>
     */
    private function knownPermissions(): array
    {
        $known = json_decode((string) file_get_contents(resource_path('json/roles_permissions.json')), true);
        $names = is_array($known) && is_array($known['permissions'] ?? null) ? $known['permissions'] : [];

        return array_values(array_filter($names, is_string(...)));
    }
};
