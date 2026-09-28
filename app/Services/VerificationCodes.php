<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * One-time codes emailed to a user, kept apart per purpose so one flow's code
 * can't be spent on another. A code is bound to the address it was sent to,
 * expires after CODE_MINUTES and is discarded after too many wrong guesses so
 * it can't be brute-forced, and only a few codes are issued per day.
 */
class VerificationCodes
{
    public const CODE_MINUTES = 15;

    public const PURPOSE_EMAIL_VERIFICATION = 'email-verification';

    public const PURPOSE_SOCIAL_LINK = 'social-link';

    public const PURPOSE_EMAIL_CHANGE = 'email-change';

    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 5;

    private const MAX_CODES_PER_DAY = 10;

    private const SECONDS_PER_DAY = 86400;

    /**
     * Create a fresh code for the given address (the user's current one by
     * default), replacing any previous one for the same purpose.
     */
    public function issue(User $user, string $purpose, ?string $address = null): string
    {
        $this->ensureBelowDailyLimit($user, $purpose);

        $code = sprintf('%06d', random_int(0, 999999));
        $expiresAt = Carbon::now()->addMinutes(self::CODE_MINUTES);

        cache()->put($this->cacheKey($user, $purpose), [
            'hash' => $this->hash($code),
            'email' => $address ?? $user->email,
            'attempts' => 0,
            'expires_at' => $expiresAt->getTimestamp(),
        ], $expiresAt);

        return $code;
    }

    /**
     * Whether the code matches the pending one. Attempts are counted under a
     * lock so parallel guesses can't skip the attempt limit.
     */
    public function verify(User $user, string $purpose, string $code, ?string $address = null): bool
    {
        $cacheKey = $this->cacheKey($user, $purpose);

        try {
            return cache()
                ->lock($cacheKey.':lock', self::LOCK_SECONDS)
                ->block(self::LOCK_SECONDS, fn (): bool => $this->check($address ?? $user->email, $cacheKey, $code));
        } catch (LockTimeoutException) {
            return false;
        }
    }

    /**
     * Each code allows only a few guesses, so a fresh code on every request
     * would let guesses go on forever, while flooding the inbox it's sent to.
     *
     * @throws ValidationException when the user was already sent today's maximum.
     */
    private function ensureBelowDailyLimit(User $user, string $purpose): void
    {
        $limiterKey = 'issued-'.$this->cacheKey($user, $purpose);

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_CODES_PER_DAY) === true) {
            throw ValidationException::withMessages([
                'code' => __('Too many codes were requested today. Please try again tomorrow.'),
            ]);
        }

        RateLimiter::hit($limiterKey, self::SECONDS_PER_DAY);
    }

    private function check(string $address, string $cacheKey, string $code): bool
    {
        $pending = cache()->get($cacheKey);

        if ($pending === null || $pending['email'] !== $address) {
            return false;
        }

        if (hash_equals($pending['hash'], $this->hash($code))) {
            cache()->forget($cacheKey);

            return true;
        }

        $pending['attempts']++;

        if ($pending['attempts'] >= self::MAX_ATTEMPTS) {
            cache()->forget($cacheKey);
        } else {
            cache()->put($cacheKey, $pending, Carbon::createFromTimestamp($pending['expires_at']));
        }

        return false;
    }

    private function cacheKey(User $user, string $purpose): string
    {
        return $purpose.'-code:'.$user->id;
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', $code, config('app.key'));
    }
}
