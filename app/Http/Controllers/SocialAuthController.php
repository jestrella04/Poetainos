<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the external authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($service)
    {
        return Socialite::driver($service)->redirect();
    }

    /**
     * Obtain the user information from the external service.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($service)
    {
        // Get user data from the external service
        $user = Socialite::driver($service)->user();

        // Create account if it doesn't exist already
        $login = User::firstOrCreate([
            'email' => $user->getEmail()
        ], [
            'name' => $user->getName(),
            'username' => $user->getNickname(),
            'password' => bin2hex(random_bytes('10')),
        ]);

        // Grab avatar
        if (empty($login->extra_info['avatar'])) {
            $updated = true;
            $avatar = basename($user->getAvatar());
            Storage::disk('local')->put($avatar, file_get_contents($user->getAvatar()));
            $login->extra_info = ['avatar' => $avatar];
        }

        // Set email as verified
        if (empty($login->email_verfied_at)) {
            $updated = true;
            $login->email_verified_at = now();
        }

        // Save changes, if any
        if ($updated ?? false) {
            $login->save();
        }

        // Log the user in
        Auth::login($login);
        return redirect(route('home'));
    }
}
