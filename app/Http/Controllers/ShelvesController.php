<?php

namespace App\Http\Controllers;

use App\Models\Writing;
use App\Services\Reactions\ReactionToggler;
use App\Services\Reactions\ShelfReaction;

class ShelvesController extends Controller
{
    /**
     * Toggles the shelving: adds the writing to the user's shelf, or takes it off when it is already there.
     *
     * @return array{method: string, count: int}
     */
    public function store(Writing $writing, ReactionToggler $toggler): array
    {
        return $toggler->toggle(new ShelfReaction($writing), $this->requireAuthUser());
    }
}
