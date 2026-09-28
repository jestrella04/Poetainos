<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\Like;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Support\Facades\DB;

/**
 * Deletes a writing, comment or user together with everything that refers to
 * it and the database can't cascade to: polymorphic likes and complaints,
 * notifications and stored images. Database rows go all or nothing; image files are removed
 * once the rows are gone, so a rollback never leaves a record without its image.
 */
class ContentDeleter
{
    public function __construct(private ImageStorage $images) {}

    public function deleteWriting(Writing $writing): void
    {
        $cover = $writing->cover;

        DB::transaction(function () use ($writing): void {
            // Comments cascade with the writing, so their ids are collected first
            $commentIds = $writing->comments()->pluck('id')->all();

            $writing->deleteOrFail();

            $this->deleteTraces([$writing->id], $commentIds);
        });

        $this->images->delete($cover);
    }

    public function deleteComment(Comment $comment): void
    {
        DB::transaction(function () use ($comment): void {
            $comment->deleteOrFail();

            $this->deleteTraces([], [$comment->id]);
        });
    }

    public function deleteUser(User $user): void
    {
        $writings = $user->writings()->get(['id', 'cover']);
        $images = $writings->map(fn (Writing $writing): ?string => $writing->cover)
            ->push($user->profile->avatar);

        DB::transaction(function () use ($user, $writings): void {
            // Their writings and comments cascade with the user, so their ids are collected first
            $commentIds = Comment::whereIn('writing_id', $writings->modelKeys())
                ->orWhere('user_id', $user->id)
                ->pluck('id')
                ->all();

            $user->deleteOrFail();

            $user->notifications()->delete();
            DB::table('notifications')->where('data->user_id', $user->id)->delete();
            $user->givenLikes()->delete();
            Complaint::where('complainable_type', User::class)->where('complainable_id', $user->id)->delete();
            $this->deleteTraces($writings->modelKeys(), $commentIds);
        });

        $images->each(fn (?string $path) => $this->images->delete($path));
    }

    /**
     * Remove the likes, complaints and notifications left pointing at content
     * that no longer exists, as deletions made before this cleanup existed left them.
     *
     * @return array{likes: int, complaints: int, notifications: int}
     */
    public function deleteOrphans(): array
    {
        return DB::transaction(fn (): array => [
            'likes' => $this->deleteOrphanedLikes(Writing::class, 'writings')
                + $this->deleteOrphanedLikes(Comment::class, 'comments'),
            'complaints' => $this->deleteOrphanedComplaints(Writing::class, 'writings')
                + $this->deleteOrphanedComplaints(Comment::class, 'comments')
                + $this->deleteOrphanedComplaints(User::class, 'users'),
            'notifications' => $this->deleteOrphanedNotifications('writing_id', 'writings')
                + $this->deleteOrphanedNotifications('comment_id', 'comments')
                + $this->deleteOrphanedNotifications('user_id', 'users'),
        ]);
    }

    /**
     * Delete the likes of, the complaints about and the notifications about
     * the given writings and comments.
     *
     * @param  array<int, int>  $writingIds
     * @param  array<int, int>  $commentIds
     */
    private function deleteTraces(array $writingIds, array $commentIds): void
    {
        foreach ([Writing::class => $writingIds, Comment::class => $commentIds] as $contentType => $contentIds) {
            if ($contentIds !== []) {
                Like::where('likeable_type', $contentType)->whereIn('likeable_id', $contentIds)->delete();
                Complaint::where('complainable_type', $contentType)->whereIn('complainable_id', $contentIds)->delete();
            }
        }

        foreach (['data->writing_id' => $writingIds, 'data->comment_id' => $commentIds] as $column => $ids) {
            if ($ids !== []) {
                DB::table('notifications')->whereIn($column, $ids)->delete();
            }
        }
    }

    /**
     * @param  class-string  $likeableType
     */
    private function deleteOrphanedLikes(string $likeableType, string $table): int
    {
        return Like::where('likeable_type', $likeableType)
            ->whereNotIn('likeable_id', DB::table($table)->select('id'))
            ->delete();
    }

    /**
     * @param  class-string  $complainableType
     */
    private function deleteOrphanedComplaints(string $complainableType, string $table): int
    {
        return Complaint::where('complainable_type', $complainableType)
            ->whereNotIn('complainable_id', DB::table($table)->select('id'))
            ->delete();
    }

    private function deleteOrphanedNotifications(string $key, string $table): int
    {
        return DB::table('notifications')
            ->whereNotNull('data->'.$key)
            ->whereNotIn('data->'.$key, DB::table($table)->select('id'))
            ->delete();
    }
}
