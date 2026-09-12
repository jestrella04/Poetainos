<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the external authentication page.
     */
    public function redirectToProvider(string $service): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (! empty(request('redirect'))) {
            Redirect::setIntendedUrl(request('redirect'));
        }

        return Socialite::driver($service)->redirect();
    }

    /**
     * Obtain the user information from the external service.
     */
    public function handleProviderCallback(string $service): RedirectResponse
    {
        // Get user data from the external service
        $social = Socialite::driver($service)->user();
        $email = (string) $social->getEmail();
        $exists = User::where('email', $email)->exists();
        $nick = $social->getNickname() ?? explode('@', $email)[0];

        // Check if user already exists
        // If not, one will be created
        $user = User::firstOrCreate([
            'email' => $email,
        ], [
            'name' => $social->getName(),
            'username' => slugify('users', $nick, 'username', '_'),
            'password' => Hash::make(bin2hex(random_bytes(10))),
            'role_id' => Role::where('name', 'user')->firstOrFail()->id,
        ]);

        // Grab avatar
        if (empty($user->extra_info['avatar']) && $social->getAvatar() !== null) {
            $avatar = @file_get_contents($social->getAvatar());
            $size = $avatar !== false ? getimagesizefromstring($avatar) : false;

            if ($size !== false) {
                $updated = true;
                $extension = image_type_to_extension($size[2]);
                $base = bin2hex(random_bytes(20));
                $path = 'avatars/'.$base.$extension;
                Storage::disk('local')->put($path, $avatar);
                $user->extra_info = ['avatar' => $path];
            }
        }

        // Social login implies a trusted email address (the provider already
        // authenticated it) — verify it once on first login. Socialite's User
        // object has no portable "email verified" flag across our providers
        // (Google/Facebook/Twitter), so check our own record instead.
        if (empty($user->email_verified_at)) {
            $updated = true;
            $user->email_verified_at = Carbon::now();
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
            $message = 'accounts.welcome-aboard';
        }

        // Set flash message
        request()->session()->flash('message', $message);

        return redirect(Redirect::intended(RouteServiceProvider::HOME)->getTargetUrl());
    }
}
