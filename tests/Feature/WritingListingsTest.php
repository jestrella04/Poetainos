<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('sorting ties', function (): void {
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
