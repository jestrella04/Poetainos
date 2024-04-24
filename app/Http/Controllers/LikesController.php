<?php

namespace App\Http\Controllers;

use App\Notifications\WritingLiked;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use Illuminate\Http\Request;

class LikesController extends Controller
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
    public function store($likeable, $likeable_id)
    {
        $like = new Like;
        $like->user_id = auth()->user()->id;
        $like->vote = 1;

        if ('writing' == $likeable) {
            $like->likeable()->associate(Writing::find($likeable_id));
        } elseif ('comment' == $likeable) {
            $like->likeable()->associate(Comment::find($likeable_id));
        }

        // Check existence
        $exist = Like::where('user_id', $like->user_id)
            ->where('likeable_type', $like->likeable_type)
            ->where('likeable_id', $like->likeable_id)
            ->count();

        if ($exist > 0) {
            return $this->destroy($likeable, $likeable_id);
        }

        $like->save();

        // Update aura
        $like->user->updateAura();

        if ('writing' == $likeable) {
            $like->likeable->updateAura();

            // Notify writing author
            if ($like->likeable->author->isNot(auth()->user())) {
                $like->likeable->author->notify(
                    new WritingLiked($like->likeable, auth()->user())
                );
            }
        }

        if ('comment' == $likeable) {
            // Notify comment author
            if ($like->likeable->author->isNot(auth()->user())) {
                $like->likeable->author->notify(
                    new CommentLiked($like->likeable, auth()->user())
                );
            }
        }

        return [
            'method' => 'store',
            'count' => $like->likeable->likes()->count(),
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function show(Like $like)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function edit(Like $like)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Like $like)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function destroy($likeable, $likeable_id)
    {
        return []; // TODO: review

        if ('writing' == $likeable) {
            Writing::find($likeable_id)->likes()->where('user_id', auth()->user()->id)->delete();
            $count = Writing::find($likeable_id)->likes()->count();
        } elseif ('comment' == $likeable) {
            Comment::find($likeable_id)->likes()->where('user_id', auth()->user()->id)->delete();
            $count = Comment::find($likeable_id)->likes()->count();
        }

        return [
            'method' => 'destroy',
            'count' => $count,
        ];
    }
}
