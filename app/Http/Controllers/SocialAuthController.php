<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
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
        if (!empty(request('redirect'))) {
            Redirect::setIntendedUrl(request('redirect'));
        }

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
        $social = Socialite::driver($service)->user();
        $exists = User::where('email', $social->getEmail())->exists();
        $nick = $social->getNickname() ?? explode('@', $social->getEmail())[0];

        // Check if user already exists
        // If not, one will be created
        $user = User::firstOrCreate([
            'email' => $social->getEmail()
        ], [
            'name' => $social->getName(),
            'username' => slugify('users', $nick, 'username', '_'),
            'password' => Hash::make(bin2hex(random_bytes(10))),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Grab avatar
        if (empty($user->extra_info['avatar'])) {
            $updated = true;
            $avatar = file_get_contents($social->getAvatar());
            $size = getimagesize($social->getAvatar());
            $extension = image_type_to_extension($size[2]);
            $base = bin2hex(random_bytes(20));
            $path = 'avatars/' . $base . $extension;
            Storage::disk('local')->put($path, $avatar);
            $user->extra_info = ['avatar' => $path];
        }

        // Set email as verified
        if (empty($social->email_verfied_at)) {
            $updated = true;
            $user->email_verified_at = now();
        }

        // Save changes, if any
        if ($updated ?? false) {
            $user->save();
        }

        // Authenticate user
        Auth::login($user);

        // Set flash message content
        if ($exists) {
            $message = 'accounts.welcome-back';
        } else {
            $message  =  'accounts.welcome-aboard';
        }

        // Set flash message
        request()->session()->flash('message', $message);

        return redirect(Redirect::intended(RouteServiceProvider::HOME)->getTargetUrl());
    }
}
