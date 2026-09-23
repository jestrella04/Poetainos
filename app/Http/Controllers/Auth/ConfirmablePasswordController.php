<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }

    /**
     * Confirm the user's password.
     *
     * @return JsonResponse|array<int|string, mixed>
     */
    public function store(Request $request): JsonResponse|array
    {
        $user = $request->user();

        if ($user === null || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => [
                    'password' => [__('The provided password does not match our records.')],
                ],
            ], 422);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return [];
    }
}
