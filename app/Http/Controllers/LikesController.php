<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Writing;
use App\Services\Reactions\LikeReaction;
use App\Services\Reactions\ReactionToggler;

class LikesController extends Controller
{
    /**
     * Toggles the like: creates it if the user hasn't liked this resource
     * yet, or removes it if they already have.
     *
     * @return array{method: string, count: int}
     */
    public function store(string $likeable, string $likeableId, ReactionToggler $toggler): array
    {
        return $toggler->toggle(
            new LikeReaction($this->resolveLikeable($likeable, $likeableId)),
            $this->requireAuthUser(),
        );
    }

    /**
     * Resolve the polymorphic target of a like from its route type and id.
     */
    private function resolveLikeable(string $likeable, string $likeableId): Writing|Comment
    {
        if (! is_numeric($likeableId)) {
            abort(404);
        }

        return match ($likeable) {
            'writing' => Writing::findOrFail((int) $likeableId),
            'comment' => Comment::findOrFail((int) $likeableId),
            default => abort(404),
        };
    }
}
