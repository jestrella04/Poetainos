<?php

use App\Models\Page;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * @param  array<string, mixed>  $attributes
 */
function createPage(array $attributes = []): Page
{
    $title = fakeTitle();
    $page = new Page;
    $page->forceFill(array_merge([
        'title' => $title,
        'slug' => Str::slug($title),
        'text' => fakeText(100),
    ], $attributes))->save();

    return $page;
}

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

describe('showing a page', function (): void {
    it('fills in the site settings the text refers to', function (): void {
        // Given
        $before = fake()->sentence();
        $after = fake()->sentence();
        $page = createPage(['text' => "{$before} {{name}} {$after}"]);

        // When
        $response = get($page->path());

        // Then
        $response->assertOk()->assertInertia(fn ($inertia) => $inertia
            ->component('pages/PoPagesShow')
            ->where('page.text', "{$before} ".getSiteConfig('name')." {$after}"));
    });
});

describe('saving a page from the admin area', function (): void {
    it('creates a page with a slug made from its title', function (): void {
        // Given
        $admin = actingAsAdmin();
        $title = fakeTitle();

        // When
        $response = actingAs($admin)->putJson(route('admin.pages.edit'), [
            'id' => 0,
            'title' => $title,
            'text' => fakeText(100),
        ]);

        // Then
        $page = Page::where('title', $title)->firstOrFail();
        $response->assertOk()->assertJson(['action' => 'create', 'id' => $page->id]);
        expect($page->slug)->toBe(Str::slug($title));
    });

    it('updates an existing page and keeps its slug', function (): void {
        // Given
        $page = createPage();
        $originalSlug = $page->slug;
        $admin = actingAsAdmin();
        $newTitle = fakeTitle();

        // When
        $response = actingAs($admin)->putJson(route('admin.pages.edit'), [
            'id' => $page->id,
            'title' => $newTitle,
            'text' => fakeText(100),
        ]);

        // Then
        $response->assertOk()->assertJson(['action' => 'update', 'id' => $page->id]);
        $page->refresh();
        expect($page->title)->toBe($newTitle);
        expect($page->slug)->toBe($originalSlug);
    });

    it('rejects a title another page already uses', function (): void {
        // Given
        $takenPage = createPage();
        $page = createPage();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->putJson(route('admin.pages.edit'), [
            'id' => $page->id,
            'title' => $takenPage->title,
            'text' => fakeText(100),
        ]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('title');
    });

    it('rejects a text shorter than one hundred characters', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->putJson(route('admin.pages.edit'), [
            'id' => 0,
            'title' => fakeTitle(),
            'text' => fake()->lexify(str_repeat('?', 99)),
        ]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('text');
        expect(Page::count())->toBe(0);
    });

    it('is forbidden for non-admins', function (): void {
        // Given
        $page = createPage();
        $originalTitle = $page->title;

        // When
        $response = actingAs(createUser())->putJson(route('admin.pages.edit'), [
            'id' => $page->id,
            'title' => fakeTitle(),
            'text' => fakeText(100),
        ]);

        // Then
        $response->assertForbidden();
        expect($page->refresh()->title)->toBe($originalTitle);
    });
});

describe('deleting a page from the admin area', function (): void {
    it('removes the page', function (): void {
        // Given
        $page = createPage();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->deleteJson(route('admin.pages.destroy', $page));

        // Then
        $response->assertOk();
        expect(Page::find($page->id))->toBeNull();
    });

    it('is forbidden for non-admins', function (): void {
        // Given
        $page = createPage();

        // When
        $response = actingAs(createUser())->deleteJson(route('admin.pages.destroy', $page));

        // Then
        $response->assertForbidden();
        expect(Page::find($page->id))->not->toBeNull();
    });
});
