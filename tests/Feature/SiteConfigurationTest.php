<?php

use App\Models\Setting;
use App\Services\SiteSettings;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('site configuration guard', function (): void {
    beforeEach(function (): void {
        config(['poetainos' => null]);
    });

    it('shows the not configured message when there are no site settings', function (): void {
        // When
        $response = get('/explore');

        // Then
        $response->assertStatus(503);
        $response->assertSee(__('The site has not been configured yet.'));
    });

    it('answers JSON requests with a JSON message when there are no site settings', function (): void {
        // When
        $response = getJson('/manifest.json');

        // Then
        $response->assertStatus(503);
        $response->assertJsonPath('message', __('The site has not been configured yet.'));
    });

    it('loads the site settings into config when they exist', function (): void {
        // Given
        Setting::create(['name' => 'site', 'data' => ['name' => 'My Site', 'slogan' => 'Hello']]);

        // When
        $response = getJson('/manifest.json');

        // Then
        $response->assertOk();
        $response->assertJsonPath('name', 'My Site');
        expect(getSiteConfig('slogan'))->toBe('Hello');
    });
});

describe('the site settings service', function (): void {
    beforeEach(function (): void {
        config(['poetainos' => null]);
    });

    it('loads the settings outside of an HTTP request', function (): void {
        // Given
        Setting::create(['name' => 'site', 'data' => ['name' => 'My Site']]);

        // When
        $loaded = app(SiteSettings::class)->load();

        // Then
        expect($loaded)->toBeTrue();
        expect(getSiteConfig('name'))->toBe('My Site');
    });

    it('does not remember that the settings were missing', function (): void {
        // Given
        expect(app(SiteSettings::class)->load())->toBeFalse();
        Setting::create(['name' => 'site', 'data' => ['name' => 'My Site']]);

        // When
        $loaded = app(SiteSettings::class)->load();

        // Then
        expect($loaded)->toBeTrue();
    });

    it('serves the cached settings without querying the database again', function (): void {
        // Given
        Setting::create(['name' => 'site', 'data' => ['name' => 'Cached']]);
        app(SiteSettings::class)->load();
        config(['poetainos' => null]);
        Setting::query()->delete();

        // When
        $loaded = app(SiteSettings::class)->load();

        // Then
        expect($loaded)->toBeTrue();
        expect(getSiteConfig('name'))->toBe('Cached');
    });

    it('picks up changed settings when refreshed', function (): void {
        // Given
        $setting = Setting::create(['name' => 'site', 'data' => ['name' => 'Before']]);
        app(SiteSettings::class)->load();
        $setting->update(['data' => ['name' => 'After']]);

        // When
        app(SiteSettings::class)->refresh();

        // Then
        expect(getSiteConfig('name'))->toBe('After');
    });
});
