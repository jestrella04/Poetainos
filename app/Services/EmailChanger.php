<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\EmailChangeRequested;
use App\Notifications\VerifyEmailCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Moves an account to a new email address only once the new address proves
 * it receives mail, while the current address is told about the request.
 * Until then the account keeps signing in and being notified at its current
 * address, so a hijacked session can't take the account over with a change.
 */
class EmailChanger
{
    public function __construct(private VerificationCodes $codes) {}

    /**
     * Hold the new address as pending, email it a code and warn the current address.
     */
    public function request(User $user, string $newEmail): void
    {
        $user->pending_email = $newEmail;
        $user->save();

        $this->sendCode($user);
        $user->notify(new EmailChangeRequested($newEmail));
    }

    /**
     * Swap in the pending address when the code matches it, the address
     * being verified from then on.
     *
     * @throws ValidationException when another account took the address in the meantime.
     */
    public function confirm(User $user, string $code): bool
    {
        $pendingEmail = $user->pending_email;

        if ($pendingEmail === null || $this->codes->verify($user, VerificationCodes::PURPOSE_EMAIL_CHANGE, $code, $pendingEmail) === false) {
            return false;
        }

        if (User::where('email', $pendingEmail)->whereKeyNot($user->id)->exists()) {
            $this->cancel($user);

            throw ValidationException::withMessages([
                'code' => __('That email address is already in use by another account.'),
            ]);
        }

        $user->email = $pendingEmail;
        $user->email_verified_at = Carbon::now();
        $user->pending_email = null;
        $user->save();

        return true;
    }

    /**
     * Drop the pending address, keeping the current one.
     */
    public function cancel(User $user): void
    {
        $user->pending_email = null;
        $user->save();
    }

    /**
     * Email a fresh code to the pending address, replacing any earlier one.
     */
    public function sendCode(User $user): void
    {
        $pendingEmail = (string) $user->pending_email;
        $code = $this->codes->issue($user, VerificationCodes::PURPOSE_EMAIL_CHANGE, $pendingEmail);

        Notification::route('mail', $pendingEmail)->notify(new VerifyEmailCode($code, VerificationCodes::CODE_MINUTES));
    }
}
