<?php

use Illuminate\Cache\FileStore;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\travel;

beforeEach(function (): void {
    $directory = storage_path('framework/testing/cache-'.uniqid());
    File::ensureDirectoryExists($directory);
    config(['cache.stores.file.path' => $directory]);
    cache()->forgetDriver('file');
});

afterEach(function (): void {
    File::deleteDirectory(config('cache.stores.file.path'));
});

function fileCacheStore(): FileStore
{
    /** @var FileStore $store */
    $store = cache()->store('file')->getStore();

    return $store;
}

describe('the cache:prune-expired command', function (): void {
    it('deletes expired entries and keeps live ones', function (): void {
        // Given
        cache()->store('file')->put('short-lived', true, 60);
        cache()->store('file')->put('long-lived', true, 3600);
        cache()->store('file')->forever('permanent', true);
        travel(2)->minutes();

        // When
        pendingArtisan('cache:prune-expired')->assertSuccessful();

        // Then
        expect(File::exists(fileCacheStore()->path('short-lived')))->toBeFalse();
        expect(File::exists(fileCacheStore()->path('long-lived')))->toBeTrue();
        expect(File::exists(fileCacheStore()->path('permanent')))->toBeTrue();
    });

    it('leaves files that are not cache entries alone', function (): void {
        // Given
        $unrelated = config('cache.stores.file.path').'/notes.txt';
        File::put($unrelated, fake()->sentence());

        // When
        pendingArtisan('cache:prune-expired')->assertSuccessful();

        // Then
        expect(File::exists($unrelated))->toBeTrue();
    });

    it('is scheduled daily', function (): void {
        // When
        $events = collect(app(Schedule::class)->events());

        // Then
        expect($events->contains(fn ($event) => $event->command !== null && str_contains($event->command, 'cache:prune-expired')))->toBeTrue();
    });
});
