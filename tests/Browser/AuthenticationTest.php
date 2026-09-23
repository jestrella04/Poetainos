<?php

use Tests\Browser\Pages\LoginPage;

describe('login happy path', function () {
    it('logs a user in through the real UI flow and lands authenticated', function () {
        $password = fake()->password();
        $user = createUserWithPassword($password);

        LoginPage::open()
            ->loginWithEmail($user->email, $password)
            ->browser()
            ->assertPathIs('/')
            ->assertNoJavaScriptErrors();
    });
});
