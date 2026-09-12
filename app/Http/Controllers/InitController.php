<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class InitController extends Controller
{
    public function init(): RedirectResponse
    {
        $token = config('services.installer.token');

        if (empty($token) || ! hash_equals($token, (string) request('token'))) {
            abort(403);
        }

        if (Setting::where('name', 'site')->first() !== null) {
            abort(403, 'App already initialized');
        }

        // Validate the admin credentials being bootstrapped
        request()->validate([
            'username' => ['required', 'string', 'min:3', 'max:45', 'regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$/'],
            'email' => ['required', 'string', 'email', 'max:250'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Create default JSON settings
        $site = (string) file_get_contents(base_path('resources/json/settings.default.json'));
        $site = str_replace('{{site_name}}', '', $site);
        $site = str_replace('{{site_slogan}}', '', $site);

        Setting::create([
            'name' => 'site',
            'data' => json_decode($site),
        ]);

        // Create default master role
        $extra_info = ['permissions' => []];
        $permissions = json_decode((string) file_get_contents(base_path('resources/json/roles_permissions.json')));
        $permissions = $permissions->permissions;

        foreach ($permissions as $permission) {
            $extra_info['permissions'][] = [
                'name' => $permission,
                'enabled' => true,
            ];
        }

        $role = Role::create([
            'name' => 'master',
            'description' => 'Master role with all privileges enabled by default',
            'extra_info' => $extra_info,
        ]);

        $user = User::create([
            'username' => request('username'),
            'role_id' => $role->id,
            'email' => request('email'),
            'password' => Hash::make((string) request('password')),
        ]);

        // Authenticate admin user
        auth()->login($user);

        // Redirect to the init success page
        return redirect(route(('home')));
    }
}
