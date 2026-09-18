<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('site installation command', function (): void {
    it('bootstraps the site with the master role and administrator', function (): void {
        // When
        $this->artisan('site:install', [
            '--username' => 'admin',
            '--email' => 'admin@example.com',
            '--password' => 'Str0ngPassw0rd',
        ])->assertSuccessful();

        // Then
        expect(Setting::where('name', 'site')->exists())->toBeTrue();

        $admin = User::where('username', 'admin')->firstOrFail();
        expect($admin->email)->toBe('admin@example.com');
        expect(Hash::check('Str0ngPassw0rd', $admin->password))->toBeTrue();
        expect($admin->role?->name)->toBe('master');
        expect($admin->isAllowed('admin'))->toBeTrue();
    });

    it('prompts for the credentials that were not given as options', function (): void {
        // When
        $this->artisan('site:install')
            ->expectsQuestion('Username', 'admin')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password', 'Str0ngPassw0rd')
            ->assertSuccessful();

        // Then
        expect(User::where('username', 'admin')->exists())->toBeTrue();
    });

    it('rejects invalid credentials without creating anything', function (): void {
        // When
        $this->artisan('site:install', [
            '--username' => 'admin',
            '--email' => 'not-an-email',
            '--password' => 'short',
        ])->assertFailed();

        // Then
        expect(Setting::where('name', 'site')->exists())->toBeFalse();
        expect(User::count())->toBe(0);
    });

    it('refuses to run once the site is already installed', function (): void {
        // Given
        Setting::create(['name' => 'site', 'data' => []]);

        // When
        $this->artisan('site:install', [
            '--username' => 'admin',
            '--email' => 'admin@example.com',
            '--password' => 'Str0ngPassw0rd',
        ])->assertFailed();

        // Then
        expect(User::count())->toBe(0);
    });
});
