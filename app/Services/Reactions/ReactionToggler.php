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
     * @return array{isActive: bool, count: int}
     */
    public function toggle(Reaction $reaction, User $actor): array
    {
        if ($reaction->isActive($actor) === true) {
            $reaction->remove($actor);
            RecalculateAura::dispatch($actor, $reaction->writing());

            return ['isActive' => false, 'count' => $reaction->count()];
        }

        try {
            $reaction->add($actor);
        } catch (UniqueConstraintViolationException) {
            // A double click already created it
            return ['isActive' => true, 'count' => $reaction->count()];
        }

        RecalculateAura::dispatch($actor, $reaction->writing());

        $author = $reaction->author();

        // Authors aren't told about reactions from the users they blocked
        if ($author !== null && $author->isNot($actor) && $author->isAuthorBlocked($actor) === false) {
            $author->notify($reaction->notification($actor));
        }

        return ['isActive' => true, 'count' => $reaction->count()];
    }
}
