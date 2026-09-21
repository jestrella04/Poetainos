<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:45', 'unique:users', 'regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$/'],
            'email' => ['required', 'string', 'email', 'max:250', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/'],
            'service_agreement' => ['required', 'accepted'],
            'privacy_agreement' => ['required', 'accepted'],
        ]);

        $user = User::unguarded(fn (): User => User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_updated_at' => Carbon::now(),
            'extra_info' => [
                'agreement' => [
                    'terms_of_use' => $request->service_agreement,
                    'privacy_policy' => $request->privacy_agreement,
                ],
            ],
            'role_id' => Role::where('name', 'user')->firstOrFail()->id,
        ]));

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        return Inertia::render('auth/PoVerify');
    }
}
