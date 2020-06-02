<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Self_;

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
        return $this->hasMany(Writing::class);
    }

    public static function popular($count = 20)
    {
        return Self::withCount('writings')->orderByDesc('writings_count')->take($count)->get();
    }
}
