<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
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
        $user = auth()->user();

        // Check existence
        $exist = Shelf::where('user_id', $user->id)->where('writing_id', $writing->id)->count();

        if ($exist > 0) {
            return $this->destroy($writing);
        }

        Shelf::create([
            'writing_id' => $writing->id,
            'user_id' => $user->id,
        ]);

        // Update aura / karma
        $user->updateAura();
        $writing->updateAura();

        // Notify author
        if (! $writing->author->is($user)) {
            $writing->author->notify(new WritingShelved($writing, $user));
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
        auth()->user()->shelf()->detach($writing->id);
        $count = Shelf::where('writing_id', $writing->id)->count();

        return [
            'method' => 'destroy',
            'count' => $count,
        ];
    }
}
