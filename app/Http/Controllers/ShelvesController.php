<?php

namespace App\Http\Controllers;

use App\Notifications\WritingShelved;
use App\Shelf;
use App\User;
use App\Writing;
use Illuminate\Http\Request;

class ShelvesController extends Controller
{
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
        ]);

        $writingId = request('id');
        $userId = auth()->user()->id;

        // Check existence
        $old = Shelf::where('user_id', $userId)->where('writing_id', $writingId)->count();

        if (0 === $old) {
            Shelf::create([
                'writing_id' => $writingId,
                'user_id' => $userId,
            ]);

            // Update aura
            User::find($userId)->updateAura();
            Writing::find($writingId)->updateAura();

            // Notify author
            if (! Writing::find($writingId)->author->is(auth()->user())) {
                Writing::find($writingId)->author->notify(new WritingShelved(Writing::find($writingId), auth()->user()));
            }
        }

        $count = ReadableHumanNumber(Shelf::where('writing_id', $writingId)->count());

        return [
            'count' => $count,
        ];
    }
}
