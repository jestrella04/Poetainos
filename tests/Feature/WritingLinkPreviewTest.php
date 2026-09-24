<?php

use App\Models\Writing;

use function Pest\Laravel\get;

describe('the excerpt of a writing', function (): void {
    it('keeps a short text whole, on a single line', function (): void {
        // Given
        $writing = Writing::factory()->make(['text' => "First verse\n\n  second verse "]);

        // When
        $excerpt = $writing->excerpt();

        // Then
        expect($excerpt)->toBe('First verse second verse');
    });

    it('cuts a long text at a word boundary', function (): void {
        // Given
        $writing = Writing::factory()->make(['text' => 'The quiet river, carrying stones']);

        // When
        $excerpt = $writing->excerpt(20);

        // Then
        expect($excerpt)->toBe('The quiet river…');
    });
});

describe('the link preview of a writing', function (): void {
    beforeEach(function (): void {
        config(['inertia.testing.ensure_pages_exist' => false]);
    });

    it('describes the page with the excerpt and the absolute cover URL', function (): void {
        // Given
        $cover = 'covers/'.fake()->uuid().'.jpg';
        $writing = Writing::factory()->create(['extra_info' => ['cover' => $cover]]);

        // When
        $response = get($writing->path());

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page
            ->where('meta.description', $writing->excerpt())
            ->where('meta.image', asset('storage/'.$cover)));
    });

    it('leaves the image to the default when the writing has no cover', function (): void {
        // Given
        $writing = Writing::factory()->create(['extra_info' => null]);

        // When
        $response = get($writing->path());

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->where('meta.image', null));
    });
});
