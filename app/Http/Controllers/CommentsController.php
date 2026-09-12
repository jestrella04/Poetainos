<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Paginator<int, Comment>
     */
    public function index(string $writing): Paginator
    {
        $filter = [0];

        $user = auth()->user();

        if ($user !== null) {
            $filter = $user->blockedAuthors()->pluck('blocked_user_id');
        }

        $comments = Comment::where('writing_id', $writing)
            ->whereNotIn('user_id', $filter)
            ->with([
                'author' => function ($query): void {
                    $query->forAuthorSummary();
                },
            ])
            ->withCount(['likes'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);

        return $comments;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): void
    {
        request()->validate([
            'comment' => 'required|min:1|max:300',
            'writing_id' => 'required|exists:writings,id',
        ]);

        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $message = request('comment');
        $comment = Comment::create([
            'user_id' => $user->id,
            'writing_id' => request('writing_id'),
            'message' => $message,
        ]);

        $writing = $comment->writing;

        // Update aura / karma
        $comment->author?->updateAura();
        $writing?->updateAura();

        // Notify author
        if ($writing !== null && $writing->author !== null && ! $writing->author->is($user)) {
            $writing->author->notify(new WritingCommented($writing, $user));
        }

        // Notify @mentions
        $mentionPattern = '/\B@[a-zA-Z0-9_-]+/';
        preg_match_all($mentionPattern, $comment->message, $mentions, PREG_PATTERN_ORDER);
        $mentions = array_unique($mentions[0]);

        foreach ($mentions as $mention) {
            $mention = User::where('username', '=', substr($mention, 1))->first();

            if (
                $mention !== null
                && $writing !== null
                && $writing->author !== null
                && ! $mention->is($writing->author)
                && ! $mention->is($user)
            ) {
                $mention->notify(new WritingCommentMentioned($comment, $user));
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<int, mixed>
     */
    public function destroy(Comment $comment): array
    {
        $this->authorize('delete', $comment);
        $comment->deleteOrFail();

        // Delete related notifications
        DB::table('notifications')->where('data->comment_id', $comment->id)->delete();

        // Delete related likes
        Like::where([
            ['likeable_type', Comment::class],
            ['likeable_id', $comment->id],
        ])->delete();

        return [];
    }
}
