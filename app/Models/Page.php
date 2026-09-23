<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPage
 */
class Page extends Model
{
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function path(): string
    {
        return route('pages.show', $this->slug);
    }
}
