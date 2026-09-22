<?php

use App\Models\Setting;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

/**
 * @return array<string, mixed>
 */
function validSiteSettings(): array
{
    return [
        'name' => ['value' => fakeTitle()],
        'slogan' => ['value' => ''],
        'pagination' => ['value' => fake()->numberBetween(1, 100)],
        'uploads_max_file_size' => ['value' => fake()->numberBetween(512, 8192)],
        'aura' => ['points' => ['writing' => ['like' => ['value' => fake()->numberBetween(0, 10)]]]],
    ];
}

describe('updating the site settings', function (): void {
    beforeEach(function (): void {
        $this->storedSettings = ['name' => ['value' => fakeTitle()]];
        Setting::create(['name' => 'site', 'data' => $this->storedSettings]);
    });

    it('saves valid settings and reloads them into config', function (): void {
        // Given
        $admin = actingAsAdmin();
        $settings = validSiteSettings();

        // When
        $response = actingAs($admin)->putJson(route('admin.settings.edit'), [
            'json' => json_encode($settings),
        ]);

        // Then
        $response->assertOk();
        expect(Setting::where('name', 'site')->value('data'))->toBe($settings);
        expect(getSiteConfig('name'))->toBe($settings['name']['value']);
        expect(getSiteConfig('pagination'))->toBe($settings['pagination']['value']);
    });

    it('rejects values that would break the site', function (string $path, mixed $value): void {
        // Given
        $admin = actingAsAdmin();
        $settings = validSiteSettings();
        data_set($settings, $path, $value);

        // When
        $response = actingAs($admin)->putJson(route('admin.settings.edit'), [
            'json' => json_encode($settings),
        ]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('json');
        expect(Setting::where('name', 'site')->value('data'))->toBe($this->storedSettings);
    })->with([
        'a text pagination' => ['pagination.value', 'abc'],
        'a zero pagination' => ['pagination.value', 0],
        'a huge pagination' => ['pagination.value', 5000],
        'a negative aura weight' => ['aura.points.writing.like.value', -1],
    ]);

    it('rejects a JSON document that is not an object', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->putJson(route('admin.settings.edit'), ['json' => '123']);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('json');
    });

    it('is forbidden to non-admins', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->putJson(route('admin.settings.edit'), [
            'json' => json_encode(validSiteSettings()),
        ]);

        // Then
        $response->assertForbidden();
    });

    it('is inaccessible to guests', function (): void {
        // When
        $response = putJson(route('admin.settings.edit'), ['json' => json_encode(validSiteSettings())]);

        // Then
        $response->assertUnauthorized();
    });
});
