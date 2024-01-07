<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:45', 'unique:users', 'regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$/'],
            'email' => ['required', 'string', 'email', 'max:45', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/'],
            'service_agreement' => ['required', 'accepted'],
            'privacy_agreement' => ['required', 'accepted'],
        ]);

        $user =  User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_updated_at' => Carbon::now(),
            'extra_info' => [
                'agreement' => [
                    'terms_of_use' => $request->service_agreement,
                    'privacy_policy' => $request->privacy_agreement,
                ]
            ]
        ]);

        //event(new Registered($user));

        Auth::login($user);

        return Inertia::render('auth/PoVerify');
        //return redirect(RouteServiceProvider::HOME);
        //Redirect::intended(RouteServiceProvider::HOME)->getTargetUrl();
    }
}
