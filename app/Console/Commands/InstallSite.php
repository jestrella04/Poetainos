<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstallSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:install
                            {--username= : Username of the master administrator}
                            {--email= : Email of the master administrator}
                            {--password= : Password of the master administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the default site settings, the master role and its administrator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (Setting::where('name', 'site')->exists()) {
            $this->error('The site is already installed.');

            return self::FAILURE;
        }

        $credentials = $this->askForCredentials();

        $validator = Validator::make($credentials, [
            'username' => ['required', 'string', 'min:3', 'max:45', 'regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$/'],
            'email' => ['required', 'string', 'email', 'max:250'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($credentials): void {
            $this->createSettings();
            $role = $this->createMasterRole();

            User::create([
                'username' => $credentials['username'],
                'role_id' => $role->id,
                'email' => $credentials['email'],
                'password' => Hash::make($credentials['password']),
            ]);
        });

        $this->info('The site was installed successfully!');

        return self::SUCCESS;
    }

    /**
     * Read the administrator credentials from the options, prompting for the missing ones.
     *
     * @return array{username: string, email: string, password: string}
     */
    private function askForCredentials(): array
    {
        return [
            'username' => (string) ($this->option('username') ?? $this->ask('Username')),
            'email' => (string) ($this->option('email') ?? $this->ask('Email')),
            'password' => (string) ($this->option('password') ?? $this->secret('Password')),
        ];
    }

    private function createSettings(): void
    {
        $site = (string) file_get_contents(base_path('resources/json/settings.default.json'));
        $site = str_replace('{{site_name}}', '', $site);
        $site = str_replace('{{site_slogan}}', '', $site);

        Setting::create([
            'name' => 'site',
            'data' => json_decode($site),
        ]);
    }

    private function createMasterRole(): Role
    {
        $permissions = json_decode((string) file_get_contents(base_path('resources/json/roles_permissions.json')));
        $extraInfo = ['permissions' => []];

        foreach ($permissions->permissions as $permission) {
            $extraInfo['permissions'][] = [
                'name' => $permission,
                'enabled' => true,
            ];
        }

        return Role::create([
            'name' => 'master',
            'description' => 'Master role with all privileges enabled by default',
            'extra_info' => $extraInfo,
        ]);
    }
}
