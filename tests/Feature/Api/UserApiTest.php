<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('the authenticated user endpoint', function (): void {
    it('is inaccessible to guests', function (): void {
        // When
        $response = getJson('/api/user');

        // Then
        $response->assertUnauthorized();
    });

    it('lets an authenticated user fetch themselves', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->getJson('/api/user');

        // Then
        $response->assertOk()->assertJson([
            'id' => $user->id,
            'username' => $user->username,
        ]);
    });
});
