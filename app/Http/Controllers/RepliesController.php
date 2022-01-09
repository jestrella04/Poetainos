<?php

namespace App\Http\Controllers;

use App\Notifications\WritingCommented;
use App\Notifications\WritingReplyMentioned;
use App\Notifications\WritingReplied;
use App\Reply;
use App\User;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'reply' => 'required|min:2|max:300',
            'comment_id' => 'required|exists:comments,id',
        ]);

        $reply = Reply::create([
            'comment_id' => request('comment_id'),
            'user_id' => auth()->user()->id,
            'message' => request('reply')
        ]);

        // Update aura
        $reply->author->updateAura();
        $reply->comment->writing->updateAura();

        // Notify author
        if (! $reply->comment->writing->author->is(auth()->user())) {
            $reply->comment->writing->author->notify(new WritingCommented($reply->comment->writing, auth()->user()));
        }

        // Notify commenter
        if (! $reply->comment->author->is(auth()->user())) {
            $reply->comment->author->notify(new WritingReplied($reply->comment->writing, auth()->user()));
        }

        // Notify @mentions
        $mentionPattern = '/\B@[a-zA-Z0-9_-]+/';
        preg_match_all($mentionPattern, $reply->message, $mentions, PREG_PATTERN_ORDER);
        $mentions = array_unique($mentions[0]);

        foreach ($mentions as $mention) {
            $mention = User::where('username', '=', substr($mention, 1))->first();

            if (
                null !== $mention
                && !$mention->is($reply->comment->writing->author)
                && !$mention->is($reply->comment->author)
                && !$mention->is(auth()->user())
            ) {
                $mention->notify(new WritingReplyMentioned($reply, auth()->user()));
            }
        }

        return view('comments.replies.show', [
            'reply' => $reply,
        ])->render();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function show(Reply $reply)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function edit(Reply $reply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reply $reply)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reply $reply)
    {
        //
    }
}
