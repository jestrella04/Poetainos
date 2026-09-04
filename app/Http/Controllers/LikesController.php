<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Writing;
use App\Notifications\CommentLiked;
use App\Notifications\WritingLiked;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LikesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
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
     * @param  Request  $request
     * @return array
     */
    public function store($likeable, $likeable_id)
    {
        $like = new Like;
        $like->user_id = auth()->user()->id;
        $like->vote = 1;

        if ($likeable == 'writing') {
            $like->likeable()->associate(Writing::find($likeable_id));
        } elseif ($likeable == 'comment') {
            $like->likeable()->associate(Comment::find($likeable_id));
        }

        // Check existence
        $exist = Like::where([
            ['user_id', $like->user_id],
            ['likeable_type', $like->likeable_type],
            ['likeable_id', $like->likeable_id],
        ])->count();

        if ($exist > 0) {
            return $this->destroy($likeable, $likeable_id);
        }

        $like->save();

        // Update aura / karma
        $like->user->updateAura();
        // $like->user->updateKarma();

        if ($likeable == 'writing') {
            $like->likeable->updateAura();

            // Notify writing author
            if ($like->likeable->author->isNot(auth()->user())) {
                $like->likeable->author->notify(
                    new WritingLiked($like->likeable, auth()->user())
                );
            }
        }

        if ($likeable == 'comment') {
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
     * @return Response
     */
    public function show(Like $like)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Like $like)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Like $like)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Like  $like
     * @return array
     */
    public function destroy($likeable, $likeable_id)
    {
        if ($likeable == 'writing') {
            Like::where([
                ['likeable_type', 'App\Models\Writing'],
                ['likeable_id', $likeable_id],
                ['user_id', auth()->user()->id],
            ])->delete();
            $count = Writing::find($likeable_id)->likes()->count();
        } elseif ($likeable == 'comment') {
            Like::where([
                ['likeable_type', 'App\Models\Comment'],
                ['likeable_id', $likeable_id],
                ['user_id', auth()->user()->id],
            ])->delete();
            $count = Comment::find($likeable_id)->likes()->count();
        }

        return [
            'method' => 'destroy',
            'count' => $count,
        ];
    }
}
