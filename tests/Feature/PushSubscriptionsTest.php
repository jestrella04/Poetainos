<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('subscribing to push notifications', function (): void {
    it('stores the browser subscription for the user', function (): void {
        // Given
        $user = createUser();
        $endpoint = fake()->url();
        $publicKey = fake()->sha256();
        $authToken = fake()->md5();

        // When
        $response = actingAs($user)->postJson(route('push.update'), [
            'endpoint' => $endpoint,
            'publicKey' => $publicKey,
            'authToken' => $authToken,
            'contentEncoding' => 'aesgcm',
        ]);

        // Then
        $response->assertNoContent();
        $subscription = $user->pushSubscriptions()->sole();
        expect($subscription->endpoint)->toBe($endpoint);
        expect($subscription->public_key)->toBe($publicKey);
        expect($subscription->auth_token)->toBe($authToken);
    });

    it('requires an endpoint', function (): void {
        // When
        $response = actingAs(createUser())->postJson(route('push.update'), ['publicKey' => fake()->sha256()]);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('endpoint');
    });

    it('rejects guests', function (): void {
        // When
        $response = postJson(route('push.update'), ['endpoint' => fake()->url()]);

        // Then
        $response->assertUnauthorized();
    });
});

describe('unsubscribing from push notifications', function (): void {
    it('removes only the subscription for the given endpoint', function (): void {
        // Given
        $user = createUser();
        $removedEndpoint = fake()->unique()->url();
        $keptEndpoint = fake()->unique()->url();
        $user->updatePushSubscription($removedEndpoint);
        $user->updatePushSubscription($keptEndpoint);

        // When
        $response = actingAs($user)->postJson(route('push.delete'), ['endpoint' => $removedEndpoint]);

        // Then
        $response->assertNoContent();
        expect($user->pushSubscriptions()->pluck('endpoint')->all())->toBe([$keptEndpoint]);
    });

    it('requires an endpoint', function (): void {
        // When
        $response = actingAs(createUser())->postJson(route('push.delete'));

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('endpoint');
    });
});
