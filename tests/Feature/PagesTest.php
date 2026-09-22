<?php

use App\Models\Page;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * @param  array<string, mixed>  $attributes
 */
function createPage(array $attributes = []): Page
{
    $page = new Page;
    $page->forceFill(array_merge([
        'title' => 'About us',
        'slug' => 'about-us',
        'text' => str_repeat('Some page text. ', 10),
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
        $page = createPage(['text' => 'Welcome to {{name}}, the home of writers.']);

        // When
        $response = get($page->path());

        // Then
        $response->assertOk()->assertInertia(fn ($inertia) => $inertia
            ->component('pages/PoPagesShow')
            ->where('page.text', 'Welcome to Poetainos, the home of writers.'));
    });
});

describe('saving a page from the admin area', function (): void {
    it('creates a page with a slug made from its title', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->putJson(route('admin.pages.edit'), [
            'id' => 0,
            'title' => 'Terms of use',
            'text' => str_repeat('A rule. ', 20),
        ]);

        // Then
        $page = Page::where('title', 'Terms of use')->firstOrFail();
        $response->assertOk()->assertJson(['action' => 'create', 'id' => $page->id]);
        expect($page->slug)->toBe('terms-of-use');
    });

    it('updates an existing page and keeps its slug', function (): void {
        // Given
        $page = createPage();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->putJson(route('admin.pages.edit'), [
            'id' => $page->id,
            'title' => 'About the team',
            'text' => str_repeat('New text. ', 20),
        ]);

        // Then
        $response->assertOk()->assertJson(['action' => 'update', 'id' => $page->id]);
        $page->refresh();
        expect($page->title)->toBe('About the team');
        expect($page->slug)->toBe('about-us');
    });

    it('rejects a title another page already uses', function (): void {
        // Given
        createPage(['title' => 'Taken', 'slug' => 'taken']);
        $page = createPage();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->putJson(route('admin.pages.edit'), [
            'id' => $page->id,
            'title' => 'Taken',
            'text' => str_repeat('New text. ', 20),
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
            'title' => 'Too short',
            'text' => str_repeat('x', 99),
        ]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('text');
        expect(Page::count())->toBe(0);
    });

    it('is forbidden for non-admins', function (): void {
        // Given
        $page = createPage();

        // When
        $response = actingAs(createUser())->putJson(route('admin.pages.edit'), [
            'id' => $page->id,
            'title' => 'Hijacked',
            'text' => str_repeat('New text. ', 20),
        ]);

        // Then
        $response->assertForbidden();
        expect($page->refresh()->title)->toBe('About us');
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
