<?php

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Tag;
use App\Models\Writing;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

describe('the admin tables', function (): void {
    $tables = [
        'categories' => ['admin.categories', 'admin/PoAdminCategories', fn (int $count) => Category::factory()->count($count)->create()],
        'tags' => ['admin.tags', 'admin/PoAdminTags', fn (int $count) => Tag::factory()->count($count)->create()],
        'writings' => ['admin.writings', 'admin/PoAdminWritings', fn (int $count) => Writing::factory()->count($count)->create()],
        'complaints' => ['admin.complaints', 'admin/PoAdminComplaints', fn (int $count) => Complaint::factory()->for(Writing::factory(), 'complainable')->count($count)->create()],
    ];

    it('render the table page with the total row count', function (string $route, string $component, Closure $seed): void {
        // Given
        $count = fake()->numberBetween(2, 5);
        $seed($count);
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->get(route($route));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page
            ->component($component)
            ->where('total', $count)
            ->has('meta.title'));
    })->with($tables);

    it('answer JSON requests with a page of rows', function (string $route, string $component, Closure $seed): void {
        // Given
        $count = fake()->numberBetween(2, 5);
        $seed($count);
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->getJson(route($route));

        // Then
        $response->assertOk()->assertJsonCount($count, 'data');
    })->with($tables);

    it('list the users, including the admin', function (): void {
        // Given
        createUser();
        $admin = actingAsAdmin();

        // When
        $page = actingAs($admin)->get(route('admin.users'));
        $rows = actingAs($admin)->getJson(route('admin.users'));

        // Then
        $page->assertInertia(fn ($inertia) => $inertia->where('total', 2));
        $rows->assertJsonCount(2, 'data');
    });

    it('list the pages', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $page = actingAs($admin)->get(route('admin.pages'));

        // Then
        $page->assertInertia(fn ($inertia) => $inertia->where('total', 0));
    });

    it('put the author with each writing', function (): void {
        // Given
        Writing::factory()->create();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->getJson(route('admin.writings'));

        // Then
        $response->assertJsonStructure(['data' => [['id', 'title', 'author' => ['id', 'username']]]]);
    });
});

describe('the admin pages', function (): void {
    it('render their component', function (string $route, string $component): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->get(route($route));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->component($component)->has('meta.title'));
    })->with([
        'dashboard' => ['admin.index', 'admin/PoAdminIndex'],
        'settings' => ['admin.settings', 'admin/PoAdminSettings'],
        'analytics' => ['admin.analytics', 'admin/PoAdminAnalytics'],
    ]);

    it('link the analytics dashboard with the configured counter credentials', function (): void {
        // Given
        $counterUser = fakeUsername();
        $counterToken = fake()->sha1();
        config(['services.counter.user_id' => $counterUser, 'services.counter.access_token' => $counterToken]);
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->get(route('admin.analytics'));

        // Then
        $response->assertInertia(fn ($page) => $page->where('counter', "https://counter.dev/dashboard.html?user={$counterUser}&token={$counterToken}"));
    });
});
