<?php

use App\Models\Tag;
use App\Models\User;

test('query returns matching tags', function () {
    Tag::factory()->create(['name' => 'poetry']);
    Tag::factory()->create(['name' => 'prose']);

    $response = $this->getJson('/tags/query?query=poe');

    $response->assertOk();
    $response->assertJsonFragment(['value' => 'poetry', 'label' => 'poetry']);
    $response->assertJsonMissing(['value' => 'prose']);
});

test('show renders for each sort option', function (string $sort) {
    $tag = Tag::factory()->create();

    $this->get($tag->path().'?sort='.$sort)->assertOk();
})->with(['latest', 'popular', 'likes']);

test('admin can delete a tag', function () {
    $admin = actingAsAdmin();
    $tag = Tag::factory()->create();

    $this->actingAs($admin)->delete('/admin/tags/delete/'.$tag->slug)->assertOk();

    expect(Tag::find($tag->id))->toBeNull();
});

test('non-admins are redirected to login for admin tag routes', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($user)
        ->delete('/admin/tags/delete/'.$tag->slug)
        ->assertRedirect(route('login'));
});
