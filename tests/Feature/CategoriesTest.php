<?php

use App\Models\Category;
use App\Models\Writing;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('the show page', function (): void {
    it('renders for each sort option', function (string $sort): void {
        // Given
        $category = Category::factory()->create();

        // When
        $response = get($category->path().'?sort='.$sort);

        // Then
        $response->assertOk();
    })->with(['latest', 'popular', 'likes']);
});

describe('writingsRecursive', function (): void {
    it('includes writings attached to descendant categories', function (): void {
        // Given
        $parent = Category::factory()->create(['parent_id' => null]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        $writing = Writing::factory()->create();
        $writing->categories()->attach($child->id);

        // When
        $writings = $parent->writingsRecursive()->pluck('id');

        // Then
        expect($writings->all())->toContain($writing->id);
    });
});

describe('admin category management', function (): void {
    it('creates and updates a category', function (): void {
        // Given
        $admin = actingAsAdmin();

        // When
        $createResponse = actingAs($admin)->put('/admin/categories/edit', [
            'id' => 0,
            'name' => 'New Category',
            'description' => 'A description long enough.',
        ]);

        // Then
        $createResponse->assertOk();
        $category = Category::where('name', 'New Category')->firstOrFail();

        // When
        $updateResponse = actingAs($admin)->put('/admin/categories/edit', [
            'id' => $category->id,
            'name' => 'New Category',
            'description' => 'An updated description.',
        ]);

        // Then
        $updateResponse->assertOk();
        expect($category->refresh()->description)->toBe('An updated description.');
    });

    it('deletes a category', function (): void {
        // Given
        $admin = actingAsAdmin();
        $category = Category::factory()->create();

        // When
        $response = actingAs($admin)->delete('/admin/categories/delete/'.$category->slug);

        // Then
        $response->assertOk();
        expect(Category::find($category->id))->toBeNull();
    });
});

describe('authorization for admin category routes', function (): void {
    it('forbids non-admins', function (): void {
        // Given
        $user = createUser();
        $category = Category::factory()->create();

        // When
        $response = actingAs($user)->delete('/admin/categories/delete/'.$category->slug);

        // Then
        $response->assertForbidden();
    });
});
