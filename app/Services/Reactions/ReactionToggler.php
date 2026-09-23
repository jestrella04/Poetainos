<?php

namespace App\Services\Reactions;

use App\Jobs\RecalculateAura;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Adds the actor's reaction when they haven't reacted yet and withdraws it when
 * they have, then refreshes the auras it affects and tells the content's author.
 */
class ReactionToggler
{
    /**
     * @return array{method: string, count: int}
     */
    public function toggle(Reaction $reaction, User $actor): array
    {
        if ($reaction->isActive($actor) === true) {
            $reaction->remove($actor);
            RecalculateAura::dispatch($actor, $reaction->writing());

            return ['method' => 'destroy', 'count' => $reaction->count()];
        }

        try {
            $reaction->add($actor);
        } catch (UniqueConstraintViolationException) {
            // A double click already created it
            return ['method' => 'store', 'count' => $reaction->count()];
        }

        RecalculateAura::dispatch($actor, $reaction->writing());

        $author = $reaction->author();

        if ($author !== null && $author->isNot($actor)) {
            $author->notify($reaction->notification($actor));
        }

        return ['method' => 'store', 'count' => $reaction->count()];
    }
}
