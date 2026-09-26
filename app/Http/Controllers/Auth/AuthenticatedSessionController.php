<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        if (isSafeRedirectPath(request('redirect'))) {
            Redirect::setIntendedUrl(request('redirect'));
        }

        return Inertia::render('auth/PoLogin', [
            'meta' => [
                'canonical' => route('login'),
            ],
            'status' => session('status'),
        ]);
    }

    /**
     * Check if an email exist.
     *
     * @return array<string, bool>
     */
    public function check(): array
    {
        // Validate user input
        request()->validate([
            'email' => 'required|email',
        ]);

        return ['exists' => User::where('email', request('email'))->exists()];
    }

    /**
     * Handle an incoming authentication request, carrying on to where the user was headed.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        Inertia::flash('message', 'accounts.welcome-back');

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Inertia::flash('message', 'accounts.logged-out-goodbye');

        return redirect('/');
    }
}
