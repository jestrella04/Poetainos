<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingLiked;
use Illuminate\Database\UniqueConstraintViolationException;

class LikesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * Toggles the like: creates it if the user hasn't liked this resource
     * yet, or removes it if they already have.
     *
     * @return array<string, mixed>
     */
    public function store(string $likeable, string $likeableId): array
    {
        $likeableModel = $this->resolveLikeable($likeable, $likeableId);
        $user = $this->requireAuthUser();

        if ($likeableModel->likes()->where('user_id', $user->id)->exists()) {
            return $this->withdrawLike($likeableModel, $user);
        }

        try {
            $likeableModel->likes()->create(['user_id' => $user->id, 'vote' => 1]);
        } catch (UniqueConstraintViolationException) {
            // A double click already created it
            return ['method' => 'store', 'count' => $likeableModel->likes()->count()];
        }

        // Update aura / karma
        $user->updateAura();

        if ($likeableModel instanceof Writing) {
            $likeableModel->updateAura();
        }

        // Notify the author of what was liked
        if ($likeableModel->author !== null && $likeableModel->author->isNot($user)) {
            $likeableModel->author->notify(
                $likeableModel instanceof Writing
                    ? new WritingLiked($likeableModel, $user)
                    : new CommentLiked($likeableModel, $user)
            );
        }

        return [
            'method' => 'store',
            'count' => $likeableModel->likes()->count(),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<string, mixed>
     */
    public function destroy(string $likeable, string $likeableId): array
    {
        return $this->withdrawLike(
            $this->resolveLikeable($likeable, $likeableId),
            $this->requireAuthUser(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function withdrawLike(Writing|Comment $likeableModel, User $user): array
    {
        $likeableModel->likes()->where('user_id', $user->id)->delete();

        // Update aura / karma
        $user->updateAura();

        if ($likeableModel instanceof Writing) {
            $likeableModel->updateAura();
        }

        return [
            'method' => 'destroy',
            'count' => $likeableModel->likes()->count(),
        ];
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
