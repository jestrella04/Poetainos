<?php

namespace App\Http\Controllers;

use App\Notifications\WritingLiked;
use App\Vote;
use Illuminate\Http\Request;

class VotesController extends Controller
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
            'id' => 'required|integer|exists:writings,id',
            'value' => 'required|integer|max:1',
        ]);

        $writingId = request('id');
        $vote = intval(request('value'));
        $userId = auth()->user()->id;

        // Check existence
        $old = Vote::where('user_id', $userId)->where('writing_id', $writingId)->count();

        if (0 === $old) {
            $new = Vote::create([
                'writing_id' => $writingId,
                'user_id' => $userId,
                'vote' => $vote,
            ]);

            // Update aura
            $new->user->updateAura();
            $new->writing->updateAura();

            // Notify author
            $new->user->notify(new WritingLiked($new->writing, auth()->user()));
        }

        if (0 === $vote) {
            $count = ReadableHumanNumber(Vote::where('vote', 0)->where('writing_id', $writingId)->count());
        } else {
            $count = ReadableHumanNumber(Vote::where('vote', '>', 0)->where('writing_id', $writingId)->count());
        }

        return [
            'created' => $new->id ?? 0,
            'count' => $count,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function show(Vote $vote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function edit(Vote $vote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vote $vote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vote $vote)
    {
        //
    }
}
