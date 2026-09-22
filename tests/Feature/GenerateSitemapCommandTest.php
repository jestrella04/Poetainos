<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Tag;
use App\Models\Writing;
use Illuminate\Support\Str;

beforeEach(function (): void {
    useTemporaryPublicPath();
});

afterEach(function (): void {
    deleteTemporaryPublicPath();
});

describe('the sitemap:generate command', function (): void {
    it('lists the site\'s public pages, built from the database', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $writing->tags()->attach($tag);
        $title = fakeTitle();
        $page = (new Page)->forceFill(['title' => $title, 'slug' => Str::slug($title), 'text' => fakeText(100)]);
        $page->save();

        // When
        pendingArtisan('sitemap:generate')->assertSuccessful();

        // Then
        $xml = file_get_contents(public_path('sitemap.xml'));
        expect($xml)
            ->toContain($writing->path())
            ->toContain($author->path())
            ->toContain($category->path())
            ->toContain($tag->path())
            ->toContain($page->path())
            ->toContain(route('explore'));
    });

    it('does not count the pages it lists as views', function (): void {
        // Given
        $views = fake()->numberBetween(0, 1000);
        $profileViews = fake()->numberBetween(0, 1000);
        $author = createUser(['profile_views' => $profileViews]);
        $writing = Writing::factory()->for($author, 'author')->create(['views' => $views]);

        // When
        pendingArtisan('sitemap:generate')->assertSuccessful();

        // Then
        expect($writing->refresh()->views)->toBe($views);
        expect($author->refresh()->profile_views)->toBe($profileViews);
    });

    it('leaves out users without writings and tags nobody used', function (): void {
        // Given
        $writer = createUser();
        $lonelyTag = Tag::factory()->create();

        // When
        pendingArtisan('sitemap:generate')->assertSuccessful();

        // Then
        expect(file_get_contents(public_path('sitemap.xml')))
            ->not->toContain($writer->path())
            ->not->toContain($lonelyTag->path());
    });
});
