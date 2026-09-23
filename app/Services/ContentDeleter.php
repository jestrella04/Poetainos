<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Support\Facades\DB;

/**
 * Deletes a writing, comment or user together with the notifications and
 * likes that refer to it, all or nothing.
 */
class ContentDeleter
{
    public function deleteWriting(Writing $writing): void
    {
        DB::transaction(function () use ($writing): void {
            $writing->deleteOrFail();

            DB::table('notifications')->where('data->writing_id', $writing->id)->delete();
            $this->deleteLikesOf(Writing::class, $writing->id);
        });
    }

    public function deleteComment(Comment $comment): void
    {
        DB::transaction(function () use ($comment): void {
            $comment->deleteOrFail();

            DB::table('notifications')->where('data->comment_id', $comment->id)->delete();
            $this->deleteLikesOf(Comment::class, $comment->id);
        });
    }

    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->deleteOrFail();

            $user->notifications()->delete();
            DB::table('notifications')->where('data->user_id', $user->id)->delete();
            $user->likes()->delete();
        });
    }

    /**
     * @param  class-string  $likeableType
     */
    private function deleteLikesOf(string $likeableType, int $likeableId): void
    {
        Like::where([
            ['likeable_type', $likeableType],
            ['likeable_id', $likeableId],
        ])->delete();
    }
}
