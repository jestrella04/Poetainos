<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
use App\Models\Writing;
use App\Notifications\WritingShelved;

class ShelvesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return array<string, mixed>
     */
    public function store(Writing $writing): array
    {
        $user = $this->requireAuthUser();

        // Check existence
        $exists = Shelf::where('user_id', $user->id)->where('writing_id', $writing->id)->exists();

        if ($exists) {
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
        if ($writing->author !== null && ! $writing->author->is($user)) {
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
     * @return array<string, mixed>
     */
    public function destroy(Writing $writing): array
    {
        $user = $this->requireAuthUser();

        $user->shelf()->detach($writing->id);
        $count = Shelf::where('writing_id', $writing->id)->count();

        return [
            'method' => 'destroy',
            'count' => $count,
        ];
    }
}
