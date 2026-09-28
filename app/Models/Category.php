<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasRecursiveRelationships;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function path(): string
    {
        return route('categories.show', $this->slug);
    }

    /**
     * @return BelongsToMany<Writing, $this>
     */
    public function writings(): BelongsToMany
    {
        return $this->belongsToMany(Writing::class);
    }

    /**
     * The writings in this category or any of its descendants.
     *
     * @return Builder<Writing>
     */
    public function writingsRecursive(): Builder
    {
        return Writing::whereIn(
            'id',
            DB::table('category_writing')
                ->select('writing_id')
                ->whereIn('category_id', $this->descendantsAndSelf()->select('id')),
        );
    }
}
