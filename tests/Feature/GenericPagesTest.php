<?php

use App\Models\Category;
use App\Models\Writing;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('the offline page', function (): void {
    it('renders', function (): void {
        // When
        $response = get('/offline');

        // Then
        $response->assertOk();
    });
});

describe('page titles', function (): void {
    beforeEach(function (): void {
        config(['inertia.testing.ensure_pages_exist' => false]);
    });

    it('carry the site name, like every other page', function (string $routeName, string $section, ?string $signIn): void {
        // When
        $response = $signIn === 'user'
            ? actingAs(createUser())->get(route($routeName))
            : get(route($routeName));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->where('meta.title', getPageTitle([__($section)])));
    })->with([
        'the contact form' => ['contact.create', 'Contact form', null],
        'the static pages index' => ['pages.index', 'Pages', null],
        'the offline page' => ['offline', 'Offline', null],
        'the notifications' => ['notifications.index', 'Notifications', 'user'],
    ]);
});

describe('the explore page', function (): void {
    it('lists the categories that have writings, main and sub categories apart, busiest first', function (): void {
        // Given
        config(['inertia.testing.ensure_pages_exist' => false]);
        $main = Category::factory()->create(['parent_id' => null]);
        $sub = Category::factory()->create(['parent_id' => $main->id]);
        Category::factory()->create(['parent_id' => null]);
        $busierSub = Category::factory()->create(['parent_id' => $main->id]);
        Writing::factory()->create()->categories()->attach([$main->id, $sub->id]);
        Writing::factory()->count(2)->create()->each(fn (Writing $writing) => $writing->categories()->attach($busierSub->id));

        // When
        $response = get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->has('categories.main', 1)
            ->where('categories.main.0.id', $main->id)
            ->has('categories.alt', 2)
            ->where('categories.alt.0.id', $busierSub->id)
            ->where('categories.alt.1.id', $sub->id));
    });
});
