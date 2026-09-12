<?php

use App\Models\User;

test('guests cannot access the authenticated user endpoint', function (): void {
    $this->getJson('/api/user')->assertUnauthorized();
});

test('an authenticated user can fetch themselves', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/user')->assertOk()->assertJson([
        'id' => $user->id,
        'username' => $user->username,
    ]);
});

test('guests cannot recalculate a user\'s karma', function (): void {
    $user = User::factory()->create();

    $this->putJson("/api/karma/{$user->username}")->assertUnauthorized();
});

test('an authenticated user can trigger a karma recalculation', function (): void {
    $user = User::factory()->create();
    $requester = User::factory()->create();

    $this->actingAs($requester)->putJson("/api/karma/{$user->username}")->assertOk();
});
