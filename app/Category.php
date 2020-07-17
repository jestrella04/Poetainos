<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
        $descendants = $this->descendantsAndSelf()->get('id')->pluck('id')->toArray();
        return $this->belongsToMany(Writing::class)->wherePivotIn('category_id', $descendants, 'or');
    }

    public function writingsCount()
    {
        return $this->writings->count();
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('categories');
    }

    public static function main()
    {
        return Self::whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public static function secondary()
    {
        return Self::whereNotNull('parent_id')
            ->orderBy('name')
            ->get();
    }
}
