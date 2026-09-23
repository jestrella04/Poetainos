<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('searching users by name or username', function (): void {
    it('finds users matching either field', function (): void {
        // Given
        $searcher = createUser();
        $searchTerm = fake()->unique()->lexify('??????????');
        createUser(['name' => fake()->firstName().' '.ucfirst($searchTerm), 'username' => fakeUsername()]);
        createUser(['name' => fake()->name(), 'username' => $searchTerm.fake()->lexify('???')]);
        createUser();

        // When
        $response = actingAs($searcher)->getJson(route('users.query', ['query' => $searchTerm]));

        // Then
        $response->assertOk()->assertJsonCount(2);
    });

    it('is only available to signed in users', function (): void {
        // When
        $response = getJson(route('users.query', ['query' => fake()->randomLetter()]));

        // Then
        $response->assertUnauthorized();
    });
});
