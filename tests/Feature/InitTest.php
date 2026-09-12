<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\get;

describe('site initialization', function (): void {
    it('is forbidden without a valid installer token', function (): void {
        // Given
        config(['services.installer.token' => 'secret-token']);

        // When
        $withoutToken = get('/init');
        $withWrongToken = get('/init?token=wrong-token');

        // Then
        $withoutToken->assertForbidden();
        $withWrongToken->assertForbidden();
        expect(Setting::where('name', 'site')->exists())->toBeFalse();
    });

    it('is forbidden when no installer token is configured', function (): void {
        // Given
        config(['services.installer.token' => null]);

        // When
        $response = get('/init?token=anything');

        // Then
        $response->assertForbidden();
    });

    it('bootstraps the site when the correct token is provided', function (): void {
        // Given
        config(['services.installer.token' => 'secret-token']);

        // When
        $response = get('/init?'.http_build_query([
            'token' => 'secret-token',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'Str0ngPassw0rd',
        ]));

        // Then
        $response->assertRedirect(route('home'));
        expect(Setting::where('name', 'site')->exists())->toBeTrue();

        assertAuthenticated();
        $admin = User::where('username', 'admin')->firstOrFail();
        expect($admin->email)->toBe('admin@example.com');
        expect(Hash::check('Str0ngPassw0rd', $admin->password))->toBeTrue();
        expect(Hash::check('', $admin->password))->toBeFalse();
        expect($admin->role?->name)->toBe('master');
        expect($admin->isAllowed('admin'))->toBeTrue();
    });

    it('rejects bootstrapping without admin credentials', function (): void {
        // Given
        config(['services.installer.token' => 'secret-token']);

        // When
        $response = get('/init?token=secret-token');

        // Then
        $response->assertInvalid(['username', 'email', 'password']);
        expect(Setting::where('name', 'site')->exists())->toBeFalse();
    });

    it('is forbidden once the site is already initialized', function (): void {
        // Given
        config(['services.installer.token' => 'secret-token']);
        Setting::create(['name' => 'site', 'data' => []]);

        // When
        $response = get('/init?token=secret-token');

        // Then
        $response->assertForbidden();
    });
});
