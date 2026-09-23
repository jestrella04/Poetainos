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

    it('forbids an unrelated authenticated user from triggering another user\'s karma recalculation', function (): void {
        // Given
        $user = createUser();
        $requester = createUser();

        // When
        $response = actingAs($requester)->putJson("/api/karma/{$user->username}");

        // Then
        $response->assertForbidden();
    });

    it('lets a user trigger their own karma recalculation', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->putJson("/api/karma/{$user->username}");

        // Then
        $response->assertOk();
    });

    it('lets an admin trigger any user\'s karma recalculation', function (): void {
        // Given
        $user = createUser();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->putJson("/api/karma/{$user->username}");

        // Then
        $response->assertOk();
    });
});
