<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Notifications\WritingCommented;
use App\Notifications\WritingCommentMentioned;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($writing)
    {
        $filter = [0];

        if (auth()->check()) {
            $filter = User::find(auth()->user()->id)->blockedAuthors()->pluck('blocked_user_id');
        }

        $comments = Comment::where('writing_id', $writing)
            ->whereNotIn('user_id', $filter)
            ->with([
                'author' => function ($query): void {
                    $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
                },
            ])
            ->withCount(['likes'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate($this->pagination);

        return $comments;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'comment' => 'required|min:1|max:300',
            'writing_id' => 'required|exists:writings,id',
        ]);

        $message = request('comment');
        $comment = Comment::create([
            'user_id' => auth()->user()->id,
            'writing_id' => request('writing_id'),
            'message' => $message,
        ]);

        // Update aura / karma
        $comment->author->updateAura();
        // $comment->author->updateKarma();
        $comment->writing->updateAura();

        // Notify author
        if (! $comment->writing->author->is(auth()->user())) {
            $comment->writing->author->notify(new WritingCommented($comment->writing, auth()->user()));
        }

        // Notify @mentions
        $mentionPattern = '/\B@[a-zA-Z0-9_-]+/';
        preg_match_all($mentionPattern, $comment->message, $mentions, PREG_PATTERN_ORDER);
        $mentions = array_unique($mentions[0]);

        foreach ($mentions as $mention) {
            $mention = User::where('username', '=', substr($mention, 1))->first();

            if (
                $mention !== null
                && ! $mention->is($comment->writing->author)
                && ! $mention->is(auth()->user())
            ) {
                $mention->notify(new WritingCommentMentioned($comment, auth()->user()));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->deleteOrFail();

        // Delete related notifications
        DatabaseNotification::where('data->comment_id', $comment->id)->delete();

        // Delete related likes
        Like::where([
            ['likeable_type', 'App\Models\Comment'],
            ['likeable_id', $comment->id],
        ])->delete();

        return [];
    }
}
