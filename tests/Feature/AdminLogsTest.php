<?php

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

afterEach(function (): void {
    deleteTemporaryLogs();
});

describe('the admin logs page', function (): void {
    it('lists the log files that can be read', function (): void {
        // Given
        $admin = actingAsAdmin();
        useTemporaryLogs(['laravel.log' => 'abc', 'ssr.log' => '']);

        // When
        $response = actingAs($admin)->get(route('admin.logs'));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/PoAdminLogs')
                ->where('files.0.name', 'laravel.log')
                ->where('files.0.size', 3)
                ->where('files.0.clearable', true)
                ->where('files.1.name', 'ssr.log'));
    });
});

describe('the admin log entries', function (): void {
    it('returns the newest entries of the chosen log, filtered by level and search', function (): void {
        // Given
        $admin = actingAsAdmin();
        $message = fake()->sentence();
        useTemporaryLogs(['laravel.log' => implode("\n", [
            logLine('error', $message),
            logLine('error', fake()->sentence()),
            logLine('debug', $message),
        ])."\n"]);

        // When
        $response = actingAs($admin)->getJson(route('admin.logs.entries', ['file' => 'laravel.log', 'level' => 'error', 'search' => $message]));

        // Then
        $response->assertOk()
            ->assertJsonPath('before', null)
            ->assertJsonCount(1, 'entries')
            ->assertJsonPath('entries.0.message', $message)
            ->assertJsonPath('entries.0.level', 'error');
    });

    it('rejects an unknown level', function (): void {
        // Given
        $admin = actingAsAdmin();
        useTemporaryLogs(['laravel.log' => '']);

        // When
        $response = actingAs($admin)->getJson(route('admin.logs.entries', ['file' => 'laravel.log', 'level' => 'verbose']));

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('level');
    });

    it('cannot read files other than the listed logs', function (string $file): void {
        // Given
        $admin = actingAsAdmin();
        useTemporaryLogs(['laravel.log' => '']);

        // When
        $response = actingAs($admin)->getJson(route('admin.logs.entries', ['file' => $file]));

        // Then
        $response->assertNotFound();
    })->with(['unknown log' => 'missing.log', 'path outside the logs' => '../../.env']);
});

describe('the admin log download', function (): void {
    it('sends the whole chosen log as a file', function (): void {
        // Given
        $admin = actingAsAdmin();
        $log = implode("\n", array_map(fn (): string => fake()->sentence(), range(1, fake()->numberBetween(2, 5))))."\n";
        useTemporaryLogs(['laravel.log' => '', 'ssr.log' => $log]);

        // When
        $response = actingAs($admin)->get(route('admin.logs.download', 'ssr.log'));

        // Then
        $response->assertOk()->assertDownload('ssr.log');
        expect($response->streamedContent())->toBe($log);
    });

    it('is not found for a file that is not a listed log', function (): void {
        // Given
        $admin = actingAsAdmin();
        useTemporaryLogs([]);

        // When
        $response = actingAs($admin)->get(route('admin.logs.download', 'missing.log'));

        // Then
        $response->assertNotFound();
    });
});

describe('clearing a log', function (): void {
    it('empties the chosen log and confirms it', function (): void {
        // Given
        $admin = actingAsAdmin();
        useTemporaryLogs(['laravel.log' => logLine('error', fake()->sentence())."\n"]);

        // When
        $response = actingAs($admin)->from(route('admin.logs'))->delete(route('admin.logs.clear', 'laravel.log'));

        // Then
        $response->assertRedirect(route('admin.logs'))
            ->assertInertiaFlash('message', 'admin.log-cleared');
        expect(file_get_contents(storage_path('logs/laravel.log')))->toBe('');
    });

    it('refuses to empty the security audit log', function (): void {
        // Given
        $admin = actingAsAdmin();
        $auditEntry = logLine('info', 'Admin action')."\n";
        useTemporaryLogs(['security-2026-09-28.log' => $auditEntry]);

        // When
        $listing = actingAs($admin)->get(route('admin.logs'));
        $response = actingAs($admin)->delete(route('admin.logs.clear', 'security-2026-09-28.log'));

        // Then
        $listing->assertInertia(fn ($page) => $page->where('files.0.clearable', false));
        $response->assertForbidden();
        expect(file_get_contents(storage_path('logs/security-2026-09-28.log')))->toBe($auditEntry);
    });

    it('is not found for a file that is not a listed log', function (): void {
        // Given
        $admin = actingAsAdmin();
        useTemporaryLogs([]);

        // When
        $response = actingAs($admin)->delete(route('admin.logs.clear', 'missing.log'));

        // Then
        $response->assertNotFound();
    });
});

it('keeps the logs away from non-admins', function (string $method, string $route, array $parameters): void {
    // Given
    $user = createUser();
    useTemporaryLogs(['laravel.log' => logLine('error', fake()->sentence())."\n"]);

    // When
    $response = actingAs($user)->{$method}(route($route, $parameters));

    // Then
    $response->assertForbidden();
    expect(file_get_contents(storage_path('logs/laravel.log')))->not->toBe('');
})->with([
    'page' => ['get', 'admin.logs', []],
    'entries' => ['getJson', 'admin.logs.entries', ['file' => 'laravel.log']],
    'download' => ['get', 'admin.logs.download', ['file' => 'laravel.log']],
    'clear' => ['delete', 'admin.logs.clear', ['file' => 'laravel.log']],
]);
