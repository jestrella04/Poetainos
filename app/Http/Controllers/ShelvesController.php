<?php

namespace App\Http\Controllers;

use App\Notifications\WritingShelved;
use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;

class ShelvesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Writing $writing)
    {
        $userId = auth()->user()->id;

        // Check existence
        $exist = Shelf::where('user_id', $userId)->where('writing_id', $writing->id)->count();

        if ($exist > 0) {
            return $this->destroy($writing);
        }

        Shelf::create([
            'writing_id' => $writing->id,
            'user_id' => $userId,
        ]);

        // Update aura / karma
        User::find($userId)->updateAura();
        //User::find($userId)->updateKarma();
        Writing::find($writing->id)->updateAura();

        // Notify author
        if (!Writing::find($writing->id)->author->is(auth()->user())) {
            Writing::find($writing->id)->author->notify(
                new WritingShelved(Writing::find($writing->id), auth()->user())
            );
        }

        $count = Shelf::where('writing_id', $writing->id)->count();

        return [
            'method' => 'store',
            'count' => $count,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Writing  $writing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Writing $writing)
    {
        User::find(auth()->user()->id)->shelf()->detach($writing->id);
        $count = Shelf::where('writing_id', $writing->id)->count();

        return [
            'method' => 'destroy',
            'count' => $count,
        ];
    }
}
