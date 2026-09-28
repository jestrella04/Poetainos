<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Get the currently authenticated user.
     */
    public function show(): User
    {
        return $this->requireAuthUser();
    }
}
