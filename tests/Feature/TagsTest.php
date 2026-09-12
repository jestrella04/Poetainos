<?php

use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

test('query returns matching tags', function (): void {
    Tag::factory()->create(['name' => 'poetry']);
    Tag::factory()->create(['name' => 'prose']);

    $response = getJson('/tags/query?query=poe');

    $response->assertOk();
    $response->assertJsonFragment(['value' => 'poetry', 'label' => 'poetry']);
    $response->assertJsonMissing(['value' => 'prose']);
});

it('show renders for each sort option', function (string $sort): void {
    $tag = Tag::factory()->create();

    get($tag->path().'?sort='.$sort)->assertOk();
})->with(['latest', 'popular', 'likes']);

test('admin can delete a tag', function (): void {
    $admin = actingAsAdmin();
    $tag = Tag::factory()->create();

    actingAs($admin)->delete('/admin/tags/delete/'.$tag->slug)->assertOk();

    expect(Tag::find($tag->id))->toBeNull();
});

test('non-admins are redirected to login for admin tag routes', function (): void {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    actingAs($user)
        ->delete('/admin/tags/delete/'.$tag->slug)
        ->assertRedirect(route('login'));
});
