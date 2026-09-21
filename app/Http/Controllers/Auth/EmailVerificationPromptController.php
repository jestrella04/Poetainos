<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        return $user !== null && $user->hasVerifiedEmail()
            ? redirect()->intended(route('home'))
            : Inertia::render('auth/PoVerify', [
                'meta' => [
                    'title' => getPageTitle([__('Verify Account')]),
                    'canonical' => route('home'),
                ],
                'status' => session('status'),
            ]);
    }
}
