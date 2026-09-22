<?php

use App\Models\User;
use Tests\Browser\Pages\LoginPage;

describe('login happy path', function () {
    it('logs a user in through the real UI flow and lands authenticated', function () {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
        ]);

        LoginPage::open()
            ->loginWithEmail($user->email, 'password')
            ->browser()
            ->assertPathIs('/')
            ->assertNoJavaScriptErrors();
    });
});
