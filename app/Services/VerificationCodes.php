<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;

/**
 * One-time codes emailed to a user, kept apart per purpose so one flow's code
 * can't be spent on another. A code is bound to the address it was sent to,
 * expires after CODE_MINUTES and is discarded after too many wrong guesses so
 * it can't be brute-forced.
 */
class VerificationCodes
{
    public const CODE_MINUTES = 15;

    public const PURPOSE_EMAIL_VERIFICATION = 'email-verification';

    public const PURPOSE_SOCIAL_LINK = 'social-link';

    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 5;

    /**
     * Create a fresh code for the user's current address, replacing any previous one for the same purpose.
     */
    public function issue(User $user, string $purpose): string
    {
        $code = sprintf('%06d', random_int(0, 999999));
        $expiresAt = Carbon::now()->addMinutes(self::CODE_MINUTES);

        cache()->put($this->cacheKey($user, $purpose), [
            'hash' => $this->hash($code),
            'email' => $user->email,
            'attempts' => 0,
            'expires_at' => $expiresAt->getTimestamp(),
        ], $expiresAt);

        return $code;
    }

    /**
     * Whether the code matches the pending one. Attempts are counted under a
     * lock so parallel guesses can't skip the attempt limit.
     */
    public function verify(User $user, string $purpose, string $code): bool
    {
        $cacheKey = $this->cacheKey($user, $purpose);

        try {
            return cache()
                ->lock($cacheKey.':lock', self::LOCK_SECONDS)
                ->block(self::LOCK_SECONDS, fn (): bool => $this->check($user, $cacheKey, $code));
        } catch (LockTimeoutException) {
            return false;
        }
    }

    private function check(User $user, string $cacheKey, string $code): bool
    {
        $pending = cache()->get($cacheKey);

        if ($pending === null || $pending['email'] !== $user->email) {
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
