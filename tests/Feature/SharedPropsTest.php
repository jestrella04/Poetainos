<?php

use App\Models\Comment;
use App\Models\Writing;
use App\Notifications\WritingShelved;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

/**
 * @return array<int, string>
 */
function queriesDuring(Closure $request): array
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $request();
    DB::disableQueryLog();

    return array_column(DB::getQueryLog(), 'query');
}

describe('the shared auth props', function (): void {
    it('tell the page what the user has liked, shelved and not yet read', function (): void {
        // Given
        $user = createUser();
        $writing = Writing::factory()->create();
        $comment = Comment::factory()->create();
        $writing->likes()->create(['user_id' => $user->id, 'vote' => 1]);
        $comment->likes()->create(['user_id' => $user->id, 'vote' => 1]);
        $user->shelf()->attach($writing);
        $user->notify(new WritingShelved($writing, createUser()));

        // When
        $response = actingAs($user)->get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('auth.liked.writings', [$writing->id])
            ->where('auth.liked.comments', [$comment->id])
            ->where('auth.shelved', [$writing->id])
            ->where('auth.notifications', 1));
    });

    it('are empty for guests', function (): void {
        // When
        $response = get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
            ->where('auth.liked.writings', [])
            ->where('auth.shelved', [])
            ->where('auth.notifications', 0));
    });

    it('cost the same number of queries however much the user has liked', function (): void {
        // Given
        $user = createUser();
        $writings = Writing::factory()->count(30)->create();
        $writings->firstOrFail()->likes()->create(['user_id' => $user->id, 'vote' => 1]);
        $withOneLike = count(queriesDuring(fn () => actingAs($user)->get(route('explore'))));

        // When
        $writings->skip(1)->each(fn (Writing $writing) => $writing->likes()->create(['user_id' => $user->id, 'vote' => 1]));
        $withManyLikes = count(queriesDuring(fn () => actingAs($user)->get(route('explore'))));

        // Then
        expect($withManyLikes)->toBe($withOneLike);
    });

    it('are not computed for JSON list requests', function (): void {
        // Given
        $user = createUser();
        Writing::factory()->count(3)->create();

        // When
        $queries = queriesDuring(fn () => actingAs($user)->getJson(route('home')));

        // Then
        expect(collect($queries)->filter(fn (string $query): bool => str_contains($query, 'from "notifications"')
            || str_contains($query, 'select "likeable_id" from "likes"')))->toBeEmpty();
    });

    it('fetch the unread notifications with a count, not by loading them', function (): void {
        // Given
        $user = createUser();

        // When
        $queries = queriesDuring(fn () => actingAs($user)->get(route('explore')));

        // Then
        expect(collect($queries)->contains(fn (string $query): bool => str_contains($query, 'count(*)') && str_contains($query, '"notifications"')))->toBeTrue();
    });
});

describe('the shared site props', function (): void {
    it('include an absolute default image URL for link previews', function (): void {
        // When
        $response = get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page->where('site.image', url('images/card.png')));
    });
});

describe('the ziggy route table', function (): void {
    it('is sent on the first visit', function (): void {
        // When
        $response = get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page->has('ziggy.routes'));
    });
});
