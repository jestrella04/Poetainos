<?php

namespace Tests\Browser\Pages;

use App\Models\Writing;

class AdminWritingsPage extends Page
{
    public static function open(): self
    {
        return new self(static::visitUrl(route('admin.writings')));
    }

    public static function writingRow(Writing $writing): string
    {
        return "#admin-writing-{$writing->id}";
    }
}
