<?php

use App\Models\Tag;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('the query endpoint', function (): void {
    it('returns matching tags', function (): void {
        // Given
        $matching = Tag::factory()->create();
        $query = mb_substr($matching->name, 0, 3);
        do {
            $otherName = fake()->unique()->word();
        } while (str_contains($otherName, $query));
        Tag::factory()->create(['name' => $otherName]);

        // When
        $response = getJson('/tags/query?query='.urlencode($query));

        // Then
        $response->assertOk();
        $response->assertJsonFragment(['value' => $matching->name, 'label' => $matching->name]);
        $response->assertJsonMissing(['value' => $otherName]);
    });

    it('refuses a query too short to narrow the tags down', function (string $query): void {
        // Given
        Tag::factory()->create();

        // When
        $response = getJson('/tags/query?query='.urlencode($query));

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('query');
    })->with(['empty' => [''], 'one letter' => ['a']]);
});

describe('the show page', function (): void {
    it('renders for each sort option', function (string $sort): void {
        // Given
        $tag = Tag::factory()->create();

        // When
        $response = get($tag->path().'?sort='.$sort);

        // Then
        $response->assertOk();
    })->with(['latest', 'popular', 'likes']);
});

describe('admin tag management', function (): void {
    it('allows an admin to delete a tag', function (): void {
        // Given
        $admin = actingAsAdmin();
        $tag = Tag::factory()->create();

        // When
        $response = actingAs($admin)->from(route('admin.tags'))->delete('/admin/tags/'.$tag->slug);

        // Then
        $response->assertRedirect(route('admin.tags'))->assertInertiaFlash('message', 'tags.tag-deleted');
        expect(Tag::find($tag->id))->toBeNull();
    });

    it('forbids non-admins', function (): void {
        // Given
        $user = createUser();
        $tag = Tag::factory()->create();

        // When
        $response = actingAs($user)->delete('/admin/tags/'.$tag->slug);

        // Then
        $response->assertForbidden();
    });
});
