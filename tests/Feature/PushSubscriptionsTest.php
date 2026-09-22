<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('subscribing to push notifications', function (): void {
    it('stores the browser subscription for the user', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->postJson(route('push.update'), [
            'endpoint' => 'https://push.example.com/abc',
            'publicKey' => 'public-key',
            'authToken' => 'auth-token',
            'contentEncoding' => 'aesgcm',
        ]);

        // Then
        $response->assertNoContent();
        $subscription = $user->pushSubscriptions()->sole();
        expect($subscription->endpoint)->toBe('https://push.example.com/abc');
        expect($subscription->public_key)->toBe('public-key');
        expect($subscription->auth_token)->toBe('auth-token');
    });

    it('requires an endpoint', function (): void {
        // When
        $response = actingAs(createUser())->postJson(route('push.update'), ['publicKey' => 'public-key']);

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('endpoint');
    });

    it('rejects guests', function (): void {
        // When
        $response = postJson(route('push.update'), ['endpoint' => 'https://push.example.com/abc']);

        // Then
        $response->assertUnauthorized();
    });
});

describe('unsubscribing from push notifications', function (): void {
    it('removes only the subscription for the given endpoint', function (): void {
        // Given
        $user = createUser();
        $user->updatePushSubscription('https://push.example.com/phone');
        $user->updatePushSubscription('https://push.example.com/laptop');

        // When
        $response = actingAs($user)->postJson(route('push.delete'), ['endpoint' => 'https://push.example.com/phone']);

        // Then
        $response->assertNoContent();
        expect($user->pushSubscriptions()->pluck('endpoint')->all())->toBe(['https://push.example.com/laptop']);
    });

    it('requires an endpoint', function (): void {
        // When
        $response = actingAs(createUser())->postJson(route('push.delete'));

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('endpoint');
    });
});
