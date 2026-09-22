<?php

namespace Tests\Browser\Pages;

use App\Models\User;

class UserProfilePage extends Page
{
    public const NAME = '#user-name';

    public static function open(User $user): self
    {
        return new self(static::visitUrl($user->path()));
    }
}
