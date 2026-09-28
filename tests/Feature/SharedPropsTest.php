<?php

use App\Http\Controllers\SocialAuthController;
use App\Models\UserProfile;
use App\Models\Writing;
use App\Notifications\WritingShelved;
use Illuminate\Support\Collection;
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
    it('tell the page how many notifications the user has not read yet', function (): void {
        // Given
        $user = createUser();
        $user->notify(new WritingShelved(Writing::factory()->create(), createUser()));

        // When
        $response = actingAs($user)->get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('auth.user.id', $user->id)
            ->where('auth.notifications', 1)
            ->missing('auth.liked')
            ->missing('auth.shelved'));
    });

    it('are empty for guests', function (): void {
        // When
        $response = get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
            ->where('auth.admin', false)
            ->where('auth.notifications', 0));
    });

    it('cost the same number of queries however much the user has liked', function (): void {
        // Given
        $user = createUser();
        $writings = Writing::factory()->count(30)->create();
        $writings->firstOrFail()->likes()->create(['user_id' => $user->id, 'vote' => 1]);
        // A fresh instance per request, as a real request loads the user anew
        $withOneLike = count(queriesDuring(fn () => actingAs($user->fresh() ?? $user)->get(route('explore'))));

        // When
        $writings->skip(1)->each(fn (Writing $writing) => $writing->likes()->create(['user_id' => $user->id, 'vote' => 1]));
        $withManyLikes = count(queriesDuring(fn () => actingAs($user->fresh() ?? $user)->get(route('explore'))));

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
        expect(collect($queries)->filter(fn (string $query): bool => str_contains($query, 'from "notifications"')))->toBeEmpty();
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

    it('leaves the admin panel routes out for everyone but admins', function (): void {
        // When
        $asGuest = get(route('explore'));
        $asUser = actingAs(createUser())->get(route('explore'));
        $asAdmin = actingAs(actingAsAdmin())->get(route('explore'));

        // Then
        // Route names contain dots, so they're looked up as keys rather than prop paths
        $hasRoute = fn (string $name): Closure => fn (Collection $routes): bool => $routes->has($name);
        $asGuest->assertInertia(fn ($page) => $page->where('ziggy.routes', $hasRoute('explore'))->whereNot('ziggy.routes', $hasRoute('admin.index')));
        $asUser->assertInertia(fn ($page) => $page->where('ziggy.routes', $hasRoute('explore'))->whereNot('ziggy.routes', $hasRoute('admin.index')));
        $asAdmin->assertInertia(fn ($page) => $page->where('ziggy.routes', $hasRoute('admin.index')));
    });
});

describe('the shared site props', function (): void {
    it('list the social networks a profile links to and the providers people sign in with', function (): void {
        // When
        $response = get(route('explore'));

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('site.socialNetworks', UserProfile::SOCIAL_NETWORKS)
            ->where('site.authProviders', SocialAuthController::PROVIDERS));
    });
});
