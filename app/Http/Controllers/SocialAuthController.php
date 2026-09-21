<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\ConfirmSocialLogin;
use App\Providers\RouteServiceProvider;
use App\Services\ImageStorage;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const AVATAR_MAX_BYTES = 2 * 1024 * 1024;

    private const AVATAR_TIMEOUT_SECONDS = 5;

    private const AVATAR_SIZE = 512;

    /**
     * Image types accepted for a provider avatar, mapped to their file extension.
     *
     * @var array<int, string>
     */
    private const AVATAR_EXTENSIONS = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];

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
    public function handleProviderCallback(string $service, ImageStorage $images): RedirectResponse
    {
        // Get user data from the external service
        $social = Socialite::driver($service)->user();
        $email = trim((string) $social->getEmail());

        // Without an email we can't tell accounts apart: every such login would share one user
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            request()->session()->flash('message', 'accounts.social-email-missing');

            return redirect(route('login'));
        }

        $exists = User::where('email', $email)->exists();
        $user = $this->findOrCreateUser($social, $email);

        // An existing account meeting this provider for the first time must
        // confirm ownership by email before we trust the provider's claim
        // and log in — otherwise anyone who can get a provider to report a
        // victim's email address could sign straight into their account.
        if ($exists && ! $this->isLinked($user, $service)) {
            return $this->askToConfirmLink($user, $service);
        }

        if (($user->extra_info['avatar'] ?? '') === '' && $social->getAvatar() !== null) {
            $this->importAvatar($user, $social->getAvatar(), $images);
        }

        // Social login implies a trusted email address (the provider already
        // authenticated it) — verify it once on first login. Socialite's User
        // object has no portable "email verified" flag across our providers
        // (Google/Facebook/Twitter), so check our own record instead.
        if ($user->email_verified_at === null) {
            $user->email_verified_at = Carbon::now();
        }

        $this->linkProvider($user, $service);
        $user->save();

        Auth::login($user);

        request()->session()->flash('message', $exists === true ? 'accounts.welcome-back' : 'accounts.welcome-aboard');

        return redirect(Redirect::intended(RouteServiceProvider::HOME)->getTargetUrl());
    }

    /**
     * Complete a social login for an existing account that just confirmed,
     * via the signed emailed link, that it owns this provider's email.
     */
    public function confirmProviderLink(string $service, User $user): RedirectResponse
    {
        $this->linkProvider($user, $service);
        $user->save();

        Auth::login($user);

        request()->session()->flash('message', 'accounts.welcome-back');

        return redirect(Redirect::intended(RouteServiceProvider::HOME)->getTargetUrl());
    }

    /**
     * The account registered with this email, created from the provider's profile on first login.
     */
    private function findOrCreateUser(SocialiteUser $social, string $email): User
    {
        $nick = $social->getNickname() ?? explode('@', $email)[0];

        return User::unguarded(fn (): User => User::firstOrCreate([
            'email' => $email,
        ], [
            'name' => $social->getName(),
            'username' => slugify('users', $nick, 'username', '_'),
            'password' => Hash::make(bin2hex(random_bytes(10))),
            'role_id' => Role::where('name', 'user')->firstOrFail()->id,
        ]));
    }

    /**
     * Email the account's owner a link to confirm that this provider may sign in to it.
     */
    private function askToConfirmLink(User $user, string $service): RedirectResponse
    {
        $confirmUrl = URL::temporarySignedRoute(
            'social.confirm',
            Carbon::now()->addMinutes(30),
            ['user' => $user->id, 'service' => $service]
        );

        $user->notify(new ConfirmSocialLogin($service, $confirmUrl));

        request()->session()->flash('message', 'accounts.confirm-social-link-sent');

        return redirect(route('login'));
    }

    /**
     * Download the provider's avatar and add it to the user's profile, keeping
     * the rest of the profile intact. Anything that isn't a small jpg, png or
     * webp image is ignored, as is a provider that can't be reached.
     */
    private function importAvatar(User $user, string $avatarUrl, ImageStorage $images): void
    {
        try {
            $response = Http::timeout(self::AVATAR_TIMEOUT_SECONDS)->get($avatarUrl);
        } catch (ConnectionException) {
            return;
        }

        $contents = $response->body();
        $imageInfo = $response->successful() && strlen($contents) <= self::AVATAR_MAX_BYTES
            ? getimagesizefromstring($contents)
            : false;

        if ($imageInfo === false || isset(self::AVATAR_EXTENSIONS[$imageInfo[2]]) === false) {
            return;
        }

        $path = $images->storeContents(
            $contents,
            self::AVATAR_EXTENSIONS[$imageInfo[2]],
            'avatars',
            self::AVATAR_SIZE,
            self::AVATAR_SIZE,
        );

        $user->extra_info = [...($user->extra_info ?? []), 'avatar' => $path];
    }

    /**
     * Whether the account already trusts this provider, so its logins skip the email confirmation step.
     */
    private function isLinked(User $user, string $service): bool
    {
        return in_array($service, $user->extra_info['linked_providers'] ?? [], true);
    }

    /**
     * Record that a provider is now trusted for this account, so future
     * logins through it skip the email confirmation step.
     */
    private function linkProvider(User $user, string $service): void
    {
        if ($this->isLinked($user, $service)) {
            return;
        }

        $extraInfo = $user->extra_info ?? [];
        $extraInfo['linked_providers'] = [...($extraInfo['linked_providers'] ?? []), $service];
        $user->extra_info = $extraInfo;
    }
}
