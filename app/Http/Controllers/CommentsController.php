<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($writing)
    {
        $filter = [0];

        if (auth()->check()) {
            $filter = User::find(auth()->user()->id)->blockedAuthors()->pluck('blocked_user_id');
        }

        $comments = Comment::where('writing_id', $writing)
            ->whereNotIn('user_id', $filter)
            ->with(['author' => function ($query) {
                $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
            }])
            ->withCount(['likes'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);

        return $comments;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'comment' => 'required|min:1|max:300',
            'writing_id' => 'required|exists:writings,id',
            'reply_to' => 'nullable|exists:users,username',
        ]);

        $message = request('comment');

        if (null !== request('reply_to')) {
            $message = '@' . request('reply_to') . ' ' . $message;
        }

        $comment = Comment::create([
            'user_id' => auth()->user()->id,
            'writing_id' => request('writing_id'),
            'message' => $message,
        ]);

        // Update aura
        $comment->author->updateAura();
        $comment->writing->updateAura();

        // Notify author
        if (!$comment->writing->author->is(auth()->user())) {
            $comment->writing->author->notify(new WritingCommented($comment->writing, auth()->user()));
        }

        // Notify @mentions
        $mentionPattern = '/\B@[a-zA-Z0-9_-]+/';
        preg_match_all($mentionPattern, $comment->message, $mentions, PREG_PATTERN_ORDER);
        $mentions = array_unique($mentions[0]);

        foreach ($mentions as $mention) {
            $mention = User::where('username', '=', substr($mention, 1))->first();

            if (
                null !== $mention
                && !$mention->is($comment->writing->author)
                && !$mention->is(auth()->user())
            ) {
                $mention->notify(new WritingCommentMentioned($comment, auth()->user()));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        DatabaseNotification::where('data->comment_id', $comment->id)->delete();

        return [];
    }
}
