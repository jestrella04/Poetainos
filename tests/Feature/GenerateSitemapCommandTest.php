<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->publicPath = sys_get_temp_dir().'/sitemap-test-'.uniqid();
    File::ensureDirectoryExists($this->publicPath);
    app()->usePublicPath($this->publicPath);
});

afterEach(function (): void {
    File::deleteDirectory($this->publicPath);
});

describe('the sitemap:generate command', function (): void {
    it('lists the site\'s public pages, built from the database', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $writing->tags()->attach($tag);
        $page = (new Page)->forceFill(['title' => 'About', 'slug' => 'about', 'text' => str_repeat('Text. ', 30)]);
        $page->save();

        // When
        $this->artisan('sitemap:generate')->assertSuccessful();

        // Then
        $xml = file_get_contents($this->publicPath.'/sitemap.xml');
        expect($xml)
            ->toContain($writing->path())
            ->toContain($writing->author->path())
            ->toContain($category->path())
            ->toContain($tag->path())
            ->toContain($page->path())
            ->toContain(route('explore'));
    });

    it('does not count the pages it lists as views', function (): void {
        // Given
        $writing = Writing::factory()->create(['views' => 7]);
        $author = $writing->author;

        // When
        $this->artisan('sitemap:generate')->assertSuccessful();

        // Then
        expect($writing->refresh()->views)->toBe(7);
        expect(User::find($author->id)->profile_views)->toBe($author->profile_views);
    });

    it('leaves out users without writings and tags nobody used', function (): void {
        // Given
        $writer = createUser();
        $lonelyTag = Tag::factory()->create();

        // When
        $this->artisan('sitemap:generate')->assertSuccessful();

        // Then
        expect(file_get_contents($this->publicPath.'/sitemap.xml'))
            ->not->toContain($writer->path())
            ->not->toContain($lonelyTag->path());
    });
});
