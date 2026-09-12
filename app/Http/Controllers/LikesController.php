<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingLiked;

class LikesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * Toggles the like: creates it if the user hasn't liked this resource
     * yet, or removes it (delegating to destroy()) if they already have.
     *
     * @return array<string, mixed>
     */
    public function store(string $likeable, string $likeableId): array
    {
        $likeableModel = $this->resolveLikeable($likeable, $likeableId);
        $user = $this->requireAuthUser();

        $like = new Like;
        $like->user()->associate($user);
        $like->vote = 1;
        $like->likeable()->associate($likeableModel);

        // Check existence
        $exists = Like::where([
            ['user_id', $like->user_id],
            ['likeable_type', $like->likeable_type],
            ['likeable_id', $like->likeable_id],
        ])->exists();

        if ($exists) {
            return $this->destroy($likeable, $likeableId);
        }

        $like->save();

        // Update aura / karma
        $user->updateAura();

        if ($likeableModel instanceof Writing) {
            $likeableModel->updateAura();

            // Notify writing author
            if ($likeableModel->author !== null && $likeableModel->author->isNot($user)) {
                $likeableModel->author->notify(
                    new WritingLiked($likeableModel, $user)
                );
            }
        }

        if ($likeableModel instanceof Comment) {
            // Notify comment author
            if ($likeableModel->author !== null && $likeableModel->author->isNot($user)) {
                $likeableModel->author->notify(
                    new CommentLiked($likeableModel, $user)
                );
            }
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
        $likeableModel = $this->resolveLikeable($likeable, $likeableId);
        $user = $this->requireAuthUser();

        Like::where([
            ['likeable_type', $likeableModel::class],
            ['likeable_id', $likeableModel->id],
            ['user_id', $user->id],
        ])->delete();

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
