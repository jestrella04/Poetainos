<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\SocialLoginCode;
use App\Services\ImageStorage;
use App\Services\VerificationCodes;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthController extends Controller
{
    private const PENDING_LINK_SESSION_KEY = 'social_link';

    private const AVATAR_SIZE = 512;

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
    public function handleProviderCallback(string $service, ImageStorage $images, VerificationCodes $codes): RedirectResponse
    {
        // A callback whose state doesn't match this session was replayed (refresh,
        // back button) or outlived the session that started it, so start over
        try {
            $social = Socialite::driver($service)->user();
        } catch (InvalidStateException) {
            request()->session()->flash('message', 'accounts.social-link-expired');

            return redirect(route('login'));
        }

        $email = trim((string) $social->getEmail());

        // Without an email we can't tell accounts apart: every such login would share one user
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            request()->session()->flash('message', 'accounts.social-email-missing');

            return redirect(route('login'));
        }

        $user = $this->findOrCreateUser($social, $email);
        $isExistingAccount = $user->wasRecentlyCreated === false;

        // An existing account meeting this provider for the first time must
        // confirm ownership with an emailed code before we trust the provider's
        // claim and log in — otherwise anyone who can get a provider to report
        // a victim's email address could sign straight into their account.
        if ($isExistingAccount === true && $this->isLinked($user, $service) === false) {
            return $this->askToConfirmLink($user, $service, $social->getAvatar(), $codes);
        }

        $this->completeLogin($user, $service, $social->getAvatar(), $images, $isExistingAccount);

        return redirect(Redirect::intended(route('home'))->getTargetUrl());
    }

    /**
     * Display the page where an existing account enters the code that confirms
     * its first sign-in through this provider.
     */
    public function showLinkConfirmation(string $service): RedirectResponse|Response
    {
        $user = $this->pendingLinkUser($service);

        if ($user === null) {
            request()->session()->flash('message', 'accounts.social-link-expired');

            return redirect(route('login'));
        }

        return Inertia::render('auth/PoSocialConfirm', [
            'meta' => [
                'title' => getPageTitle([__('Confirm Sign-in')]),
                'canonical' => route('home'),
            ],
            'service' => $service,
            'email' => $user->email,
        ]);
    }

    /**
     * Complete a social login for an existing account once it enters the code
     * emailed to it, proving it owns this provider's email address.
     *
     * @return array{url: string}
     */
    public function confirmProviderLink(string $service, ImageStorage $images, VerificationCodes $codes): array
    {
        request()->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->requirePendingLinkUser($service);

        if ($codes->verify($user, VerificationCodes::PURPOSE_SOCIAL_LINK, request('code')) === false) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or has expired.'),
            ]);
        }

        $avatarUrl = request()->session()->pull(self::PENDING_LINK_SESSION_KEY)['avatar'];
        $this->completeLogin($user, $service, $avatarUrl, $images, true);

        return ['url' => Redirect::intended(route('home'))->getTargetUrl()];
    }

    /**
     * Email a fresh confirmation code to the account awaiting its first sign-in through this provider.
     */
    public function resendLinkCode(string $service, VerificationCodes $codes): \Illuminate\Http\Response
    {
        $this->sendLinkCode($this->requirePendingLinkUser($service), $service, $codes);

        return response()->noContent();
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
     * Remember which account is awaiting confirmation and email its owner a
     * code to confirm that this provider may sign in to it.
     */
    private function askToConfirmLink(User $user, string $service, ?string $avatarUrl, VerificationCodes $codes): RedirectResponse
    {
        request()->session()->put(self::PENDING_LINK_SESSION_KEY, [
            'user_id' => $user->id,
            'service' => $service,
            'avatar' => $avatarUrl,
        ]);

        $this->sendLinkCode($user, $service, $codes);

        return redirect(route('social.confirm', $service));
    }

    private function sendLinkCode(User $user, string $service, VerificationCodes $codes): void
    {
        $code = $codes->issue($user, VerificationCodes::PURPOSE_SOCIAL_LINK);

        $user->notify(new SocialLoginCode($code, $service, VerificationCodes::CODE_MINUTES));
    }

    /**
     * The account this browser is confirming a first sign-in for through this provider, if any.
     */
    private function pendingLinkUser(string $service): ?User
    {
        $pending = request()->session()->get(self::PENDING_LINK_SESSION_KEY);

        if ($pending === null || $pending['service'] !== $service) {
            return null;
        }

        return User::whereKey($pending['user_id'])->first();
    }

    private function requirePendingLinkUser(string $service): User
    {
        $user = $this->pendingLinkUser($service);

        if ($user === null) {
            throw ValidationException::withMessages([
                'code' => __('Your sign-in request has expired. Please sign in again.'),
            ]);
        }

        return $user;
    }

    /**
     * Log the user in through a provider it is allowed to use, filling in the
     * avatar and email verification the provider vouches for.
     */
    private function completeLogin(User $user, string $service, ?string $avatarUrl, ImageStorage $images, bool $isReturning): void
    {
        if (($user->profile->avatar ?? '') === '' && $avatarUrl !== null) {
            $this->importAvatar($user, $avatarUrl, $images);
        }

        // Social login implies a trusted email address (the provider already
        // authenticated it) — verify it once on first login. Socialite's User
        // object has no portable "email verified" flag across providers, so
        // check our own record instead.
        if ($user->email_verified_at === null) {
            $user->email_verified_at = Carbon::now();
        }

        $this->linkProvider($user, $service);
        $user->save();

        Auth::login($user);

        request()->session()->flash('message', $isReturning === true ? 'accounts.welcome-back' : 'accounts.welcome-aboard');
    }

    /**
     * Add the provider's avatar to the user's profile, when it is an image we accept.
     */
    private function importAvatar(User $user, string $avatarUrl, ImageStorage $images): void
    {
        $path = $images->storeRemote($avatarUrl, 'avatars', self::AVATAR_SIZE, self::AVATAR_SIZE);

        if ($path !== null) {
            $user->editableProfile()->update(['avatar' => $path]);
        }
    }

    /**
     * Whether the account already trusts this provider, so its logins skip the email confirmation step.
     */
    private function isLinked(User $user, string $service): bool
    {
        return $user->socialAccounts()->where('provider', $service)->exists();
    }

    /**
     * Record that a provider is now trusted for this account, so future
     * logins through it skip the email confirmation step.
     */
    private function linkProvider(User $user, string $service): void
    {
        $user->socialAccounts()->firstOrCreate(['provider' => $service]);
    }
}
