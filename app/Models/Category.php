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
        $ids = $this->descendantsAndSelf()
            ->get('id')
            ->pluck('id')
            ->toArray();

        return Writing::with('categories')->whereHas('categories', function($q) use($ids) {
            $q->whereIn('category_id', $ids);
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

    public static function main()
    {
        return Self::whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public static function secondary()
    {
        return Self::withCount('writings')
            ->whereNotNull('parent_id')
            ->orderByDesc('writings_count')
            ->having('writings_count', '>', 0)
            ->get();
    }
}
