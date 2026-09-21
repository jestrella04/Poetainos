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
        'categories' => ['admin.categories', 'admin/PoAdminCategories', fn () => Category::factory()->count(3)->create()],
        'tags' => ['admin.tags', 'admin/PoAdminTags', fn () => Tag::factory()->count(3)->create()],
        'writings' => ['admin.writings', 'admin/PoAdminWritings', fn () => Writing::factory()->count(3)->create()],
        'complaints' => ['admin.complaints', 'admin/PoAdminComplaints', fn () => Complaint::factory()->for(Writing::factory(), 'complainable')->count(3)->create()],
    ];

    it('render the table page with the total row count', function (string $route, string $component, Closure $seed): void {
        // Given
        $seed();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->get(route($route));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page
            ->component($component)
            ->where('total', 3)
            ->has('meta.title'));
    })->with($tables);

    it('answer JSON requests with a page of rows', function (string $route, string $component, Closure $seed): void {
        // Given
        $seed();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->getJson(route($route));

        // Then
        $response->assertOk()->assertJsonCount(3, 'data');
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

describe('the admin tools page', function (): void {
    it('shows only the last lines of the application log', function (): void {
        // Given
        $originalStoragePath = storage_path();
        $storagePath = sys_get_temp_dir().'/poetainos-log-'.uniqid();
        mkdir($storagePath.'/logs', 0777, true);
        file_put_contents(
            $storagePath.'/logs/laravel.log',
            implode("\n", array_map(fn (int $number): string => sprintf('entry-%03d', $number), range(1, 150)))."\n",
        );
        $admin = actingAsAdmin();
        app()->useStoragePath($storagePath);

        try {
            // When
            $response = actingAs($admin)->get(route('admin.tools'));
        } finally {
            app()->useStoragePath($originalStoragePath);
            unlink($storagePath.'/logs/laravel.log');
            rmdir($storagePath.'/logs');
            rmdir($storagePath);
        }

        // Then
        $response->assertInertia(fn ($page) => $page->where('log', fn ($log): bool => str_contains((string) $log, 'entry-150')
            && str_contains((string) $log, 'entry-051')
            && ! str_contains((string) $log, 'entry-050')));
    });

    it('shows an empty log when there is no log file', function (): void {
        // Given
        $originalStoragePath = storage_path();
        $storagePath = sys_get_temp_dir().'/poetainos-nolog-'.uniqid();
        mkdir($storagePath, 0777, true);
        $admin = actingAsAdmin();
        app()->useStoragePath($storagePath);

        try {
            // When
            $response = actingAs($admin)->get(route('admin.tools'));
        } finally {
            app()->useStoragePath($originalStoragePath);
            rmdir($storagePath);
        }

        // Then
        $response->assertInertia(fn ($page) => $page->where('log', ''));
    });
});
