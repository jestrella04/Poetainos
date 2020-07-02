<?php

namespace App\Http\Controllers;

use App\Role;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InitController extends Controller
{
    public function init(Request $request)
    {
        // Validate user input
        request()->validate([
            'site_name' => 'required|string|min:3|max:100',
            'site_slogan' => 'required|string|min:10|max:255',
            'admin_username' => ['required', 'string', 'min:3', 'max:45', 'unique:users,username', 'regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$/'],
            'admin_email' => 'required|email|max:45',
            'admin_password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/'],
        ]);

        // Create default JSON settings
        $site = file_get_contents(base_path('resources/json/settings.default.json'));
        $site = str_replace('{{site_name}}', request('site_name'), $site);
        $site = str_replace('{{site_slogan}}', request('site_slogan'), $site);

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
            'username' => request('admin_username'),
            'role_id' => $role->id,
            'email' => request('admin_email'),
            'password' => Hash::make(request('admin_password')),
        ]);

        // Authenticate admin user
        Auth::login($user);

        // Set flash message
        request()->session()->flash('init', true);

        // Redirect to the init success page
        return redirect(route(('init.success')));
    }

    public function show()
    {
        return view('init.install');
    }

    public function success()
    {
        if (session('init')) {
            return view('init.success');
        }

        abort(404);
    }
}
