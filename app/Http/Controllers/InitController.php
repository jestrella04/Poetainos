<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InitController extends Controller
{
    public function init()
    {
        if (null !== Setting::where('name', 'site')->first()) {
            abort(403, 'App already initialized');
        }

        // Create default JSON settings
        $site = file_get_contents(base_path('resources/json/settings.default.json'));
        $site = str_replace('{{site_name}}', "", $site);
        $site = str_replace('{{site_slogan}}', "", $site);

        Setting::create([
            'name' => 'site',
            'data' => json_decode($site),
        ]);

        // Create default master role
        $extra_info = ['permissions' => []];
        $permissions = json_decode(file_get_contents(base_path('resources/json/roles_permissions.json')));
        $permissions = $permissions->permissions;

        foreach($permissions as $permission) {
            $extra_info['permissions'][] = [
                'name' => $permission,
                'enabled' => true
            ];
        }

        $role = Role::create([
            'name' => 'master',
            'description' => 'Master role with all privileges enabled by default',
            'extra_info' => $extra_info,
        ]);

        $user = User::create([
            'username' => "",
            'role_id' => $role->id,
            'email' => "",
            'password' => Hash::make(""),
        ]);

        // Authenticate admin user
        Auth::login($user);

        // Redirect to the init success page
        return redirect(route(('home')));
    }
}
