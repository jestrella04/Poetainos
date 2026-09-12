<?php

use App\Models\Writing;

use function Pest\Laravel\getJson;

describe('sorting ties', function (): void {
    it('breaks popular and likes ties by aura descending', function (): void {
        // Given
        $lowerAura = Writing::factory()->create(['views' => 10, 'aura' => 1.0]);
        $higherAura = Writing::factory()->create(['views' => 10, 'aura' => 5.0]);

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
        $lowerAura = Writing::factory()->for($author, 'author')->create(['views' => 10, 'aura' => 1.0]);
        $higherAura = Writing::factory()->for($author, 'author')->create(['views' => 10, 'aura' => 5.0]);

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
        $author = createUser(['karma' => 'A']);
        Writing::factory()->for($author, 'author')->create();

        // When
        $response = getJson('/?sort=latest');

        // Then
        expect($response->json('data.0.author.karma'))->toBe('A');
    });
});
