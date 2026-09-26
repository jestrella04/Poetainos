<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuraCalculator;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Get the currently authenticated user.
     */
    public function show(): User
    {
        return $this->requireAuthUser();
    }

    /**
     * Recalculate the given user's karma.
     */
    public function recalculateKarma(User $user, AuraCalculator $calculator): Response
    {
        $this->authorize('update', $user);

        $calculator->updateUserKarma($user);

        return response($user->karma);
    }
}
