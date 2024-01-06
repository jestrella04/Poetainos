<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use \Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

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

    public function writingsRecursive()
    {
        return Writing::with('categories')->whereHas('categories', function ($q) {
            $q->whereIn('category_id', $this->descendantsAndSelf()->pluck('id'));
        });
    }

    public function writingsCount()
    {
        return $this->writings->unique()->count();
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->with('categories');
    }
}
