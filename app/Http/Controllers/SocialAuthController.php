<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\ConfirmSocialLogin;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the external authentication page.
     */
    public function redirectToProvider(string $service): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (isSafeRedirectPath(request('redirect'))) {
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

        $linkedProviders = $user->extra_info['linked_providers'] ?? [];

        // An existing account meeting this provider for the first time must
        // confirm ownership by email before we trust the provider's claim
        // and log in — otherwise anyone who can get a provider to report a
        // victim's email address could sign straight into their account.
        if ($exists && ! in_array($service, $linkedProviders, true)) {
            $confirmUrl = URL::temporarySignedRoute(
                'social.confirm',
                Carbon::now()->addMinutes(30),
                ['user' => $user->id, 'service' => $service]
            );

            $user->notify(new ConfirmSocialLogin($service, $confirmUrl));

            request()->session()->flash('message', 'accounts.confirm-social-link-sent');

            return redirect(route('login'));
        }

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

        $updated = $this->linkProvider($user, $service, $linkedProviders) || ($updated ?? false);

        // Save changes, if any
        if ($updated) {
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

    /**
     * Complete a social login for an existing account that just confirmed,
     * via the signed emailed link, that it owns this provider's email.
     */
    public function confirmProviderLink(string $service, User $user): RedirectResponse
    {
        $linkedProviders = $user->extra_info['linked_providers'] ?? [];

        if ($this->linkProvider($user, $service, $linkedProviders)) {
            $user->save();
        }

        Auth::login($user);

        request()->session()->flash('message', 'accounts.welcome-back');

        return redirect(Redirect::intended(RouteServiceProvider::HOME)->getTargetUrl());
    }

    /**
     * Record that a provider is now trusted for this account, so future
     * logins through it skip the email confirmation step.
     *
     * @param  array<int, string>  $linkedProviders
     */
    private function linkProvider(User $user, string $service, array $linkedProviders): bool
    {
        if (in_array($service, $linkedProviders, true)) {
            return false;
        }

        $linkedProviders[] = $service;
        $extraInfo = $user->extra_info ?? [];
        $extraInfo['linked_providers'] = $linkedProviders;
        $user->extra_info = $extraInfo;

        return true;
    }
}
