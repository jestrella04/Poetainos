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
        $writing = Writing::factory()->create(['cover' => $cover]);

        // When
        $response = get($writing->path());

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page
            ->where('meta.description', $writing->excerpt())
            ->where('meta.image', asset('storage/'.$cover)));
    });

    it('leaves the image to the default when the writing has no cover', function (): void {
        // Given
        $writing = Writing::factory()->create(['cover' => null]);

        // When
        $response = get($writing->path());

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->where('meta.image', null));
    });
});

describe('the Open Graph tags of a writing without SSR', function (): void {
    beforeEach(function (): void {
        config(['inertia.testing.ensure_pages_exist' => false, 'inertia.ssr.enabled' => false]);
    });

    it('renders the title, description and cover in the HTML crawlers read', function (): void {
        // Given
        $cover = 'covers/'.fake()->uuid().'.jpg';
        $writing = Writing::factory()->create(['cover' => $cover]);

        // When
        $response = get($writing->path());

        // Then
        $response->assertOk()
            ->assertSee('<meta property="og:url" content="'.e($writing->path()).'"', false)
            ->assertSee('<meta property="og:title" content="'.e($writing->title), false)
            ->assertSee('<meta property="og:description" content="'.e($writing->excerpt()).'"', false)
            ->assertSee('<meta property="og:image" content="'.e(asset('storage/'.$cover)).'"', false);
    });

    it('falls back to the site card when the writing has no cover', function (): void {
        // Given
        $writing = Writing::factory()->create(['cover' => null]);

        // When
        $response = get($writing->path());

        // Then
        $response->assertOk()
            ->assertSee('<meta property="og:image" content="'.e(asset('images/card.png')).'"', false);
    });
});
