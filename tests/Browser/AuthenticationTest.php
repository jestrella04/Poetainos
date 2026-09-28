<?php

use Tests\Browser\Pages\LoginPage;

describe('login happy path', function (): void {
    it('logs a user in through the real UI flow and lands authenticated', function (): void {
        $password = fake()->password();
        $user = createUserWithPassword($password);

        LoginPage::open()
            ->loginWithEmail($user->email, $password)
            ->browser()
            ->assertPathIs('/')
            ->assertNoJavaScriptErrors();
    });
});
