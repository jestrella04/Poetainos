<?php

use App\Models\Setting;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('site configuration guard', function (): void {
    beforeEach(function (): void {
        config(['writerhood' => null]);
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
