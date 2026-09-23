<?php

namespace Tests\Browser\Pages;

class LoginPage extends Page
{
    public const CONTINUE_WITH_EMAIL_BUTTON = '#login-with-email';

    public const EMAIL_INPUT = '#login-email';

    public const PASSWORD_INPUT = '#login-password';

    public const SUBMIT_BUTTON = '#login-submit';

    public static function open(): self
    {
        return new self(static::visitUrl(route('login')));
    }

    public function loginWithEmail(string $email, string $password): static
    {
        $this->browser->click(self::CONTINUE_WITH_EMAIL_BUTTON)
            ->type(self::EMAIL_INPUT, $email)
            ->click(self::SUBMIT_BUTTON)
            ->type(self::PASSWORD_INPUT, $password)
            ->click(self::SUBMIT_BUTTON);

        return $this;
    }
}
