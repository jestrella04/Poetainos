<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Default JSON settings
        $site = file_get_contents(base_path('resources/json/settings.default.json'));
        $site = str_replace('{{site_name}}', request('site_name'), $site);
        $site = str_replace('{{site_slogan}}', request('site_slogan'), $site);

        DB::table('settings')->insert([
            ['name' => 'site', 'data' => $site],
        ]);

        // Create default master role
        $extra_info = [];
        $permissions = json_decode(file_get_contents(base_path('resources/json/roles_permissions.json')));
        $permissions = $permissions->permissions;

        foreach($permissions as $permission) {
            $extra_info[]['permission'] = [
                'name' => $permission,
                'enabled' => true
            ];
        }

        $roleId = DB::table('roles')->insertGetId([
            [
                'name' => 'master',
                'description' => 'Master role with all privileges enabled by default',
                'extra_info' => '{ "permissions": ' . json_encode($extra_info) . ' }'
            ],
        ]);

        //
        $userId = DB::table('users')->insertGetId([
            [
                'username' => request('admin_username'),
                'role_id' => $roleId,
                'email' => request('admin_email'),
                'password' => Hash::make(request('admin_password')),
            ],
        ]);

        redirect(route(('init.success')));
    }

    public function show()
    {
        return view('init.install');
    }
}
