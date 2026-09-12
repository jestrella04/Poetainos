<?php

use App\Models\Setting;

test('init is forbidden without a valid installer token', function (): void {
    config(['services.installer.token' => 'secret-token']);

    $this->get('/init')->assertForbidden();
    $this->get('/init?token=wrong-token')->assertForbidden();

    expect(Setting::where('name', 'site')->exists())->toBeFalse();
});

test('init is forbidden when no installer token is configured', function (): void {
    config(['services.installer.token' => null]);

    $this->get('/init?token=anything')->assertForbidden();
});

test('init bootstraps the site when the correct token is provided', function (): void {
    config(['services.installer.token' => 'secret-token']);

    $this->get('/init?token=secret-token')->assertRedirect(route('home'));

    expect(Setting::where('name', 'site')->exists())->toBeTrue();
});

test('init is forbidden once the site is already initialized', function (): void {
    config(['services.installer.token' => 'secret-token']);
    Setting::create(['name' => 'site', 'data' => []]);

    $this->get('/init?token=secret-token')->assertForbidden();
});
