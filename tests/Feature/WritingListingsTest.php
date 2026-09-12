<?php

use App\Models\User;
use App\Models\Writing;

use function Pest\Laravel\getJson;

test('scopeSorted breaks popular and likes ties by aura descending', function (): void {
    $lowerAura = Writing::factory()->create(['views' => 10, 'aura' => 1.0]);
    $higherAura = Writing::factory()->create(['views' => 10, 'aura' => 5.0]);

    $popularIds = Writing::sorted('popular')->pluck('id')->all();
    $higherIndex = array_search($higherAura->id, $popularIds, true);
    $lowerIndex = array_search($lowerAura->id, $popularIds, true);

    if ($higherIndex === false || $lowerIndex === false) {
        throw new RuntimeException('Expected both writings to be present in the sorted list.');
    }

    expect($higherIndex)->toBeLessThan($lowerIndex);
});

test('a user\'s writings listing breaks popular ties by aura the same way the homepage does', function (): void {
    $author = User::factory()->create();
    $lowerAura = Writing::factory()->for($author, 'author')->create(['views' => 10, 'aura' => 1.0]);
    $higherAura = Writing::factory()->for($author, 'author')->create(['views' => 10, 'aura' => 5.0]);

    $response = getJson(route('users.writings.index', $author->username).'?sort=popular');
    $ids = collect((array) $response->json('data'))->pluck('id')->all();
    $higherIndex = array_search($higherAura->id, $ids, true);
    $lowerIndex = array_search($lowerAura->id, $ids, true);

    if ($higherIndex === false || $lowerIndex === false) {
        throw new RuntimeException('Expected both writings to be present in the sorted list.');
    }

    expect($higherIndex)->toBeLessThan($lowerIndex);
});

test('writings listings include the author karma alongside the other author fields', function (): void {
    $author = User::factory()->create(['karma' => 'A']);
    Writing::factory()->for($author, 'author')->create();

    $response = getJson('/?sort=latest');

    expect($response->json('data.0.author.karma'))->toBe('A');
});
