<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * For content with an author (`user_id`) that viewers can hide by blocking the author.
 */
trait HidesBlockedAuthors
{
    /**
     * Exclude the content authored by any of the given blocked user ids.
     *
     * @param  Builder<static>  $query
     * @param  array<int>  $blockedUserIds
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, array $blockedUserIds): Builder
    {
        if ($blockedUserIds === []) {
            return $query;
        }

        return $query->whereNotIn($query->getModel()->qualifyColumn('user_id'), $blockedUserIds);
    }
}
