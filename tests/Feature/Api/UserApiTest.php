<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

test('guests cannot access the authenticated user endpoint', function (): void {
    getJson('/api/user')->assertUnauthorized();
});

test('an authenticated user can fetch themselves', function (): void {
    $user = User::factory()->create();

    actingAs($user)->getJson('/api/user')->assertOk()->assertJson([
        'id' => $user->id,
        'username' => $user->username,
    ]);
});

test('guests cannot recalculate a user\'s karma', function (): void {
    $user = User::factory()->create();

    putJson("/api/karma/{$user->username}")->assertUnauthorized();
});

test('an authenticated user can trigger a karma recalculation', function (): void {
    $user = User::factory()->create();
    $requester = User::factory()->create();

    actingAs($requester)->putJson("/api/karma/{$user->username}")->assertOk();
});
