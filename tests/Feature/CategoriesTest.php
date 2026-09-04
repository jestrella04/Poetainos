<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Writing;

test('show renders for each sort option', function (string $sort) {
    $category = Category::factory()->create();

    $this->get($category->path().'?sort='.$sort)->assertOk();
})->with(['latest', 'popular', 'likes']);

test('writingsRecursive includes writings attached to descendant categories', function () {
    $parent = Category::factory()->create(['parent_id' => null]);
    $child = Category::factory()->create(['parent_id' => $parent->id]);
    $writing = Writing::factory()->create();
    $writing->categories()->attach($child->id);

    $writings = $parent->writingsRecursive()->pluck('id');

    expect($writings->all())->toContain($writing->id);
});

test('admin can create and update a category', function () {
    $admin = actingAsAdmin();

    $this->actingAs($admin)->put('/admin/categories/edit', [
        'id' => 0,
        'name' => 'New Category',
        'description' => 'A description long enough.',
    ])->assertOk();

    $category = Category::where('name', 'New Category')->firstOrFail();

    $this->actingAs($admin)->put('/admin/categories/edit', [
        'id' => $category->id,
        'name' => 'New Category',
        'description' => 'An updated description.',
    ])->assertOk();

    expect($category->fresh()->description)->toBe('An updated description.');
});

test('admin can delete a category', function () {
    $admin = actingAsAdmin();
    $category = Category::factory()->create();

    $this->actingAs($admin)->delete('/admin/categories/delete/'.$category->slug)->assertOk();

    expect(Category::find($category->id))->toBeNull();
});

test('non-admins are redirected to login for admin category routes', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->delete('/admin/categories/delete/'.$category->slug)
        ->assertRedirect(route('login'));
});
