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
        $name = fakeTitle();
        $updatedDescription = fake()->sentence();

        // When
        $createResponse = actingAs($admin)->put('/admin/categories/edit', [
            'id' => 0,
            'name' => $name,
            'description' => fake()->sentence(),
        ]);

        // Then
        $createResponse->assertOk();
        $category = Category::where('name', $name)->firstOrFail();

        // When
        $updateResponse = actingAs($admin)->put('/admin/categories/edit', [
            'id' => $category->id,
            'name' => $name,
            'description' => $updatedDescription,
        ]);

        // Then
        $updateResponse->assertOk();
        expect($category->refresh()->description)->toBe($updatedDescription);
    });

    it('does not let a category become its own parent or move under a descendant', function (string $newParent): void {
        // Given
        $admin = actingAsAdmin();
        // The name is sent back through validation, which a short factory word could fail.
        $root = Category::factory()->create(['parent_id' => null, 'name' => fakeTitle()]);
        $child = Category::factory()->create(['parent_id' => $root->id]);
        $grandchild = Category::factory()->create(['parent_id' => $child->id]);
        $target = ['root' => $root, 'child' => $child, 'grandchild' => $grandchild][$newParent];

        // When
        $response = actingAs($admin)->putJson('/admin/categories/edit', [
            'id' => $root->id,
            'name' => $root->name,
            'parent' => $target->id,
            'description' => fake()->sentence(),
        ]);

        // Then
        $response->assertJsonValidationErrors('parent');
        expect($root->refresh()->parent_id)->toBeNull();
    })->with(['itself' => 'root', 'a child' => 'child', 'a grandchild' => 'grandchild']);

    it('lets a category move under an unrelated category', function (): void {
        // Given
        $admin = actingAsAdmin();
        // The name is sent back through validation, which a short factory word could fail.
        $root = Category::factory()->create(['parent_id' => null, 'name' => fakeTitle()]);
        $other = Category::factory()->create(['parent_id' => null]);

        // When
        $response = actingAs($admin)->putJson('/admin/categories/edit', [
            'id' => $root->id,
            'name' => $root->name,
            'parent' => $other->id,
            'description' => fake()->sentence(),
        ]);

        // Then
        $response->assertOk();
        expect($root->refresh()->parent_id)->toBe($other->id);
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
