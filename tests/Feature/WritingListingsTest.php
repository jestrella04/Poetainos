<?php

use App\Models\User;
use App\Models\Writing;

test('scopeSorted breaks popular and likes ties by aura descending', function (): void {
    $lowerAura = Writing::factory()->create(['views' => 10, 'aura' => 1.0]);
    $higherAura = Writing::factory()->create(['views' => 10, 'aura' => 5.0]);

    $popularIds = Writing::sorted('popular')->pluck('id')->all();
    expect(array_search($higherAura->id, $popularIds, true))
        ->toBeLessThan(array_search($lowerAura->id, $popularIds, true));
});

test('a user\'s writings listing breaks popular ties by aura the same way the homepage does', function (): void {
    $author = User::factory()->create();
    $lowerAura = Writing::factory()->for($author, 'author')->create(['views' => 10, 'aura' => 1.0]);
    $higherAura = Writing::factory()->for($author, 'author')->create(['views' => 10, 'aura' => 5.0]);

    $response = $this->getJson(route('users.writings.index', $author->username).'?sort=popular');
    $ids = collect($response->json('data'))->pluck('id')->all();

    expect(array_search($higherAura->id, $ids, true))
        ->toBeLessThan(array_search($lowerAura->id, $ids, true));
});

test('writings listings include the author karma alongside the other author fields', function (): void {
    $author = User::factory()->create(['karma' => 'A']);
    Writing::factory()->for($author, 'author')->create();

    $response = $this->getJson('/?sort=latest');

    expect($response->json('data.0.author.karma'))->toBe('A');
});
