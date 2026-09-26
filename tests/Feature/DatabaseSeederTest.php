<?php

use App\Models\UserProfile;
use App\Models\Writing;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\seed;

it('stores every seeded avatar and cover under an upload-style random name', function (): void {
    // Given
    Storage::fake('local');

    // When
    seed(DatabaseSeeder::class);

    // Then
    $imagePaths = UserProfile::pluck('avatar')
        ->merge(Writing::pluck('cover'))
        ->filter();

    expect($imagePaths)->not->toBeEmpty();
    $imagePaths->each(function (string $path): void {
        expect($path)->toMatch('#^(avatars|covers)/[A-Za-z0-9]{40}\.(png|jpg)$#');
        Storage::disk('local')->assertExists($path);
    });
});
