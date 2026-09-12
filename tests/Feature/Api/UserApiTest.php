<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

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

describe('recalculating karma', function (): void {
    it('is inaccessible to guests', function (): void {
        // Given
        $user = createUser();

        // When
        $response = putJson("/api/karma/{$user->username}");

        // Then
        $response->assertUnauthorized();
    });

    it('lets an authenticated user trigger a karma recalculation', function (): void {
        // Given
        $user = createUser();
        $requester = createUser();

        // When
        $response = actingAs($requester)->putJson("/api/karma/{$user->username}");

        // Then
        $response->assertOk();
    });
});
