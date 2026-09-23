<?php

use App\Models\User;

describe('user initials', function (): void {
    it('uses the first letters of the first and last words of the name', function (): void {
        // Given
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $user = User::factory()->make([
            'name' => mb_strtolower($firstName).'  '.fake()->firstName().' '.$lastName,
            'username' => fakeUsername(),
        ]);

        // When
        $initials = $user->initials();

        // Then
        expect($initials)->toBe(mb_strtoupper(mb_substr($firstName, 0, 1).mb_substr($lastName, 0, 1)));
    });

    it('uses the first letter of the name when it is a single word', function (): void {
        // Given
        // A lower case accented first letter checks the upper-casing is multibyte safe.
        $user = User::factory()->make(['name' => 'á'.fake()->lexify('????'), 'username' => fakeUsername()]);

        // When
        $initials = $user->initials();

        // Then
        expect($initials)->toBe('Á');
    });

    it('falls back to the username when the name is empty', function (?string $name): void {
        // Given
        $username = fakeUsername();
        $user = User::factory()->make(['name' => $name, 'username' => $username]);

        // When
        $initials = $user->initials();

        // Then
        expect($initials)->toBe(mb_strtoupper(mb_substr($username, 0, 1)));
    })->with([null, ' ']);
});
