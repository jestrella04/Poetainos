<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingShelved;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShelvesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
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
        // User::find($userId)->updateKarma();
        Writing::find($writing->id)->updateAura();

        // Notify author
        if (! Writing::find($writing->id)->author->is(auth()->user())) {
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
     * @return Response
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
