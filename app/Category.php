<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function path()
    {
        return route('categories.show', $this->slug);
    }

    public function writings()
    {
        return $this->belongsToMany(Writing::class);
    }

    public static function popular($count = 20)
    {
        return Self::withCount('writings')->orderByDesc('writings_count')->take($count)->get();
    }
}
