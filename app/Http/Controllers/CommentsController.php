<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateAura;
use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use App\Services\ContentDeleter;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CommentsController extends Controller
{
    private const MAX_MENTIONS = 5;

    /**
     * Display a listing of the resource.
     *
     * @return Paginator<int, Comment>
     */
    public function index(string $writingId): Paginator
    {
        $comments = Comment::where('writing_id', $writingId)
            ->visibleTo($this->blockedAuthorIds())
            ->with([
                'author' => function ($query): void {
                    $query->forAuthorSummary();
                },
            ])
            ->withCount(['likes'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->perPage);

        return $comments;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): void
    {
        $request->validate([
            'comment' => 'required|string|max:300',
            'writing_id' => 'required|exists:writings,id',
        ]);

        $user = $this->requireAuthUser();
        $writing = Writing::findOrFail((int) $request->input('writing_id'));

        $comment = $writing->comments()->create([
            'user_id' => $user->id,
            'message' => $request->input('comment'),
        ]);

        RecalculateAura::dispatch($user, $writing);

        // Notify author
        if ($writing->author !== null && $writing->author->isNot($user)) {
            $writing->author->notify(new WritingCommented($writing, $user));
        }

        $this->notifyMentions($comment, $writing, $user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<int, mixed>
     */
    public function destroy(Comment $comment, ContentDeleter $deleter): array
    {
        $this->authorize('delete', $comment);
        $deleter->deleteComment($comment);

        RecalculateAura::dispatch($comment->author, $comment->writing);

        return [];
    }

    /**
     * Notify the users @mentioned in a comment, up to MAX_MENTIONS of them.
     * The writing's author and the commenter are already covered elsewhere.
     */
    private function notifyMentions(Comment $comment, Writing $writing, User $commenter): void
    {
        preg_match_all('/\B@([a-zA-Z0-9_-]+)/', $comment->message, $matches);

        $usernames = array_slice(array_unique($matches[1]), 0, self::MAX_MENTIONS);

        User::whereIn('username', $usernames)
            ->get()
            ->reject(fn (User $mentioned): bool => $mentioned->is($commenter) || $mentioned->is($writing->author))
            ->each(fn (User $mentioned) => $mentioned->notify(new WritingCommentMentioned($comment, $commenter)));
    }
}
