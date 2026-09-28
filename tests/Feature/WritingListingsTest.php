<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Writing;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('sorting ties', function (): void {
    it('breaks any remaining tie by the newest id, so pages never overlap', function (string $sort): void {
        // Given
        $createdAt = Carbon::now()->subDay();
        $older = Writing::factory()->create(['created_at' => $createdAt, 'views' => 0, 'aura' => 0]);
        $newer = Writing::factory()->create(['created_at' => $createdAt, 'views' => 0, 'aura' => 0]);

        // When
        $ids = Writing::withListingRelations()->sorted($sort)->pluck('writings.id')->all();

        // Then
        expect($ids)->toBe([$newer->id, $older->id]);
    })->with(['latest', 'popular', 'likes']);

    it('breaks popular and likes ties by aura descending', function (): void {
        // Given
        $views = fake()->numberBetween(0, 1000);
        $lowerAura = Writing::factory()->create(['views' => $views, 'aura' => fake()->randomFloat(2, 0, 50)]);
        $higherAura = Writing::factory()->create(['views' => $views, 'aura' => $lowerAura->aura + fake()->randomFloat(2, 0.01, 50)]);

        // When
        $popularIds = Writing::sorted('popular')->pluck('id')->all();
        $higherIndex = array_search($higherAura->id, $popularIds, true);
        $lowerIndex = array_search($lowerAura->id, $popularIds, true);

        if ($higherIndex === false || $lowerIndex === false) {
            throw new RuntimeException('Expected both writings to be present in the sorted list.');
        }

        // Then
        expect($higherIndex)->toBeLessThan($lowerIndex);
    });

    it('breaks popular ties on a user\'s writings listing the same way the homepage does', function (): void {
        // Given
        $author = createUser();
        $views = fake()->numberBetween(0, 1000);
        $lowerAura = Writing::factory()->for($author, 'author')->create(['views' => $views, 'aura' => fake()->randomFloat(2, 0, 50)]);
        $higherAura = Writing::factory()->for($author, 'author')->create(['views' => $views, 'aura' => $lowerAura->aura + fake()->randomFloat(2, 0.01, 50)]);

        // When
        $response = getJson(route('users.writings.index', $author->username).'?sort=popular');
        $ids = collect((array) $response->json('data'))->pluck('id')->all();
        $higherIndex = array_search($higherAura->id, $ids, true);
        $lowerIndex = array_search($lowerAura->id, $ids, true);

        if ($higherIndex === false || $lowerIndex === false) {
            throw new RuntimeException('Expected both writings to be present in the sorted list.');
        }

        // Then
        expect($higherIndex)->toBeLessThan($lowerIndex);
    });
});

describe('listing fields', function (): void {
    it('includes the author karma alongside the other author fields', function (): void {
        // Given
        $karma = fake()->randomElement(['A', 'B', 'C', 'D', 'F']);
        $author = createUser(['karma' => $karma]);
        Writing::factory()->for($author, 'author')->create();

        // When
        $response = getJson('/?sort=latest');

        // Then
        expect($response->json('data.0.author.karma'))->toBe($karma);
    });

    it('includes the URLs of the cover and of the author avatar', function (): void {
        // Given
        $author = createUser();
        UserProfile::factory()->for($author)->create(['avatar' => 'avatars/'.fake()->uuid().'.png']);
        $writing = Writing::factory()->for($author, 'author')->create(['cover' => 'covers/'.fake()->uuid().'.jpg']);

        // When
        $response = getJson('/?sort=latest');

        // Then
        expect($response->json('data.0.cover_url'))->toBe(asset('storage/'.$writing->cover));
        expect($response->json('data.0.author.avatar_url'))->toBe(asset('storage/'.$author->profile->avatar));
    });

    it('has no cover or avatar URL when there is none', function (): void {
        // Given
        Writing::factory()->for(createUser(), 'author')->create(['cover' => null]);

        // When
        $response = getJson('/?sort=latest');

        // Then
        expect($response->json('data.0.cover_url'))->toBeNull();
        expect($response->json('data.0.author.avatar_url'))->toBeNull();
    });
});

describe('the canonical link of a listing', function (): void {
    beforeEach(function (): void {
        config(['inertia.testing.ensure_pages_exist' => false]);
    });

    it('points at the listing itself instead of the home page', function (Closure $makeListing): void {
        // Given
        [$url, $expectedCanonical] = $makeListing();

        // When
        $response = get($url);

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->where('meta.canonical', $expectedCanonical));
    })->with([
        'the home page' => [fn () => [route('home'), route('home')]],
        'the golden flowers' => [fn () => [route('writings.awards'), route('writings.awards')]],
        'a category' => [function (): array {
            $category = Category::factory()->create();

            return [$category->path(), $category->path()];
        }],
        'a tag' => [function (): array {
            $tag = Tag::factory()->create();

            return [$tag->path(), $tag->path()];
        }],
        'a user\'s writings' => [function (): array {
            $user = createUser();

            return [route('users.writings.index', $user), $user->writingsPath()];
        }],
        'a user\'s shelf' => [function (): array {
            $user = createUser();

            return [route('users.shelf.index', $user), route('users.shelf.index', $user)];
        }],
        'a user\'s likes' => [function (): array {
            $user = createUser();

            return [route('users.likes.index', $user), route('users.likes.index', $user)];
        }],
    ]);
});

describe('ranking authors', function (): void {
    it('puts the best karma first and treats missing karma as the lowest grade', function (): void {
        // Given
        // Missing karma ties with F, so the higher aura puts it ahead.
        $gradeFAura = fake()->randomFloat(2, 0, 50);
        $missingKarma = createUser(['karma' => null, 'aura' => $gradeFAura + fake()->randomFloat(2, 0.01, 50)]);
        $gradeF = createUser(['karma' => 'F', 'aura' => $gradeFAura]);
        $gradeC = createUser(['karma' => 'C', 'aura' => fake()->randomFloat(2, 0, 100)]);
        $gradeA = createUser(['karma' => 'A', 'aura' => fake()->randomFloat(2, 0, 100)]);

        // When
        $ranked = User::ranked()->pluck('id')->all();

        // Then
        expect($ranked)->toBe([$gradeA->id, $gradeC->id, $missingKarma->id, $gradeF->id]);
    });
});

describe('the viewer\'s reactions on listed writings', function (): void {
    it('mark the writings the viewer liked and shelved', function (): void {
        // Given
        $viewer = createUser();
        $liked = Writing::factory()->create();
        $shelved = Writing::factory()->create();
        $untouched = Writing::factory()->create();
        $liked->likes()->create(['user_id' => $viewer->id, 'vote' => 1]);
        $untouched->likes()->create(['user_id' => createUser()->id, 'vote' => 1]);
        $viewer->shelf()->attach($shelved);

        // When
        $writings = collect((array) actingAs($viewer)->getJson(route('home'))->json('data'))->keyBy('id');

        // Then
        expect($writings[$liked->id])->toMatchArray(['is_liked' => true, 'is_shelved' => false]);
        expect($writings[$shelved->id])->toMatchArray(['is_liked' => false, 'is_shelved' => true]);
        expect($writings[$untouched->id])->toMatchArray(['is_liked' => false, 'is_shelved' => false]);
    });

    it('are left out for guests', function (): void {
        // Given
        Writing::factory()->create();

        // When
        $writing = getJson(route('home'))->json('data.0');

        // Then
        expect($writing)->not->toHaveKeys(['is_liked', 'is_shelved']);
    });

    it('mark the writing page the viewer liked', function (): void {
        // Given
        config(['inertia.testing.ensure_pages_exist' => false]);
        $viewer = createUser();
        $writing = Writing::factory()->create();
        $writing->likes()->create(['user_id' => $viewer->id, 'vote' => 1]);

        // When
        $response = actingAs($viewer)->get($writing->path());

        // Then
        $response->assertInertia(fn ($page) => $page
            ->where('writing.is_liked', true)
            ->where('writing.is_shelved', false));
    });
});

describe('the listing excerpt', function (): void {
    it('shows the start of the text on one line, cut at a word', function (): void {
        // Given
        $words = collect(range(1, 120))->map(fn (int $number): string => 'word'.$number);
        $writing = Writing::factory()->create(['text' => $words->take(3)->implode("\n\n").' '.$words->skip(3)->implode(' ')]);

        // When
        $excerpt = (string) $writing->listing_excerpt;

        // Then
        expect($excerpt)->toStartWith('word1 word2 word3 word4')
            ->toEndWith('…')
            ->not->toContain("\n");
        expect(mb_strlen($excerpt))->toBeLessThanOrEqual(401);
        expect($words->contains(mb_substr($excerpt, (int) mb_strrpos($excerpt, ' ') + 1, -1)))->toBeTrue();
    });

    it('is left out when the text was not selected', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $listed = Writing::select('id', 'title')->findOrFail($writing->id);

        // Then
        expect($listed->toArray())->toHaveKey('listing_excerpt', null);
    });
});
