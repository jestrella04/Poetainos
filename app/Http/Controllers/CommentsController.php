<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateAura;
use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use App\Services\ContentDeleter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Inertia\Inertia;

class CommentsController extends Controller
{
    private const MAX_MENTIONS = 5;

    /**
     * Display a listing of the resource.
     *
     * @return Paginator<int, Comment>
     */
    public function index(Writing $writing): Paginator
    {
        return $writing->comments()
            ->visibleTo($this->blockedAuthorIds())
            ->with([
                'author' => function ($query): void {
                    $query->forAuthorSummary();
                },
            ])
            ->withCount(['likes'])
            ->withExists(Comment::viewerReactions())
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->simplePaginate($this->perPage);
    }

    /**
     * Store a newly created resource in storage, answering with its id.
     */
    public function store(Request $request): JsonResponse
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

        return response()->json(['id' => $comment->id], 201);
    }

    /**
     * Remove the specified resource from storage, then return to the writing.
     */
    public function destroy(Comment $comment, ContentDeleter $deleter): RedirectResponse
    {
        $this->authorize('delete', $comment);
        $deleter->deleteComment($comment);

        RecalculateAura::dispatch($comment->author, $comment->writing);

        Inertia::flash(['message' => 'comments.comment-deleted', 'color' => 'success']);

        return back();
    }

    /**
     * Notify the users @mentioned in a comment, up to MAX_MENTIONS of them.
     * The writing's author and the commenter are already covered elsewhere,
     * and users who blocked the commenter aren't told.
     */
    private function notifyMentions(Comment $comment, Writing $writing, User $commenter): void
    {
        preg_match_all(User::MENTION_PATTERN, $comment->message, $matches);

        $usernames = array_slice(array_unique(array_map(fn (string $username): string => rtrim($username, '.'), $matches[1])), 0, self::MAX_MENTIONS);

        User::whereIn('username', $usernames)
            ->whereDoesntHave('blockedAuthors', fn ($query) => $query->where('blocked_user_id', $commenter->id))
            ->get()
            ->reject(fn (User $mentioned): bool => $mentioned->is($commenter) || $mentioned->is($writing->author))
            ->each(fn (User $mentioned) => $mentioned->notify(new WritingCommentMentioned($comment, $commenter)));
    }
}
