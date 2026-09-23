<?php

namespace Tests\Browser\Pages;

class HomePage extends Page
{
    public static function open(): self
    {
        return new self(static::visitUrl(route('home')));
    }
}
