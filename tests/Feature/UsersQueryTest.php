<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('searching users by name or username', function (): void {
    it('finds users matching either field', function (): void {
        // Given
        $searcher = createUser();
        createUser(['name' => 'Emily Dickinson', 'username' => 'emily']);
        createUser(['name' => 'Walt Whitman', 'username' => 'dickinsonfan']);
        createUser(['name' => 'Someone Else', 'username' => 'other']);

        // When
        $response = actingAs($searcher)->getJson(route('users.query', ['query' => 'dickinson']));

        // Then
        $response->assertOk()->assertJsonCount(2);
    });

    it('is only available to signed in users', function (): void {
        // When
        $response = getJson(route('users.query', ['query' => 'a']));

        // Then
        $response->assertUnauthorized();
    });
});
