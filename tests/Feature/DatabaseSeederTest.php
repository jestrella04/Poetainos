<?php

use Database\Factories\WritingFactory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\seed;

it('stores the demo cover that seeded writings point at', function (): void {
    // Given
    Storage::fake('local');

    // When
    seed(DatabaseSeeder::class);

    // Then
    Storage::disk('local')->assertExists(WritingFactory::DEMO_COVER_PATH);
});
