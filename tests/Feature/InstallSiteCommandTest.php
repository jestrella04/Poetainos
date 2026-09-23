<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('site installation command', function (): void {
    it('bootstraps the site with the master role and administrator', function (): void {
        // Given
        $username = fakeUsername();
        $email = fake()->safeEmail();
        $password = fakeStrongPassword();

        // When
        pendingArtisan('site:install', [
            '--username' => $username,
            '--email' => $email,
            '--password' => $password,
        ])->assertSuccessful();

        // Then
        expect(Setting::where('name', 'site')->exists())->toBeTrue();

        $admin = User::where('username', $username)->firstOrFail();
        expect($admin->email)->toBe($email);
        expect(Hash::check($password, $admin->password))->toBeTrue();
        expect($admin->role?->name)->toBe('master');
        expect($admin->isAllowed('admin'))->toBeTrue();
    });

    it('prompts for the credentials that were not given as options', function (): void {
        // Given
        $username = fakeUsername();

        // When
        pendingArtisan('site:install')
            ->expectsQuestion('Username', $username)
            ->expectsQuestion('Email', fake()->safeEmail())
            ->expectsQuestion('Password', fakeStrongPassword())
            ->assertSuccessful();

        // Then
        expect(User::where('username', $username)->exists())->toBeTrue();
    });

    it('rejects invalid credentials without creating anything', function (): void {
        // Given
        $emailWithoutDomain = fake()->domainWord();
        $passwordUnderEightCharacters = fake()->lexify(str_repeat('?', 7));

        // When
        pendingArtisan('site:install', [
            '--username' => fakeUsername(),
            '--email' => $emailWithoutDomain,
            '--password' => $passwordUnderEightCharacters,
        ])->assertFailed();

        // Then
        expect(Setting::where('name', 'site')->exists())->toBeFalse();
        expect(User::count())->toBe(0);
    });

    it('refuses to run once the site is already installed', function (): void {
        // Given
        Setting::create(['name' => 'site', 'data' => []]);

        // When
        pendingArtisan('site:install', [
            '--username' => fakeUsername(),
            '--email' => fake()->safeEmail(),
            '--password' => fakeStrongPassword(),
        ])->assertFailed();

        // Then
        expect(User::count())->toBe(0);
    });
});
