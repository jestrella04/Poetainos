<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailChanger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * The address the signed-in user asked to move their account to, which only
 * replaces the current one once the code emailed to it is entered.
 */
class PendingEmailController extends Controller
{
    /**
     * Swap in the pending address once its code is entered, then return to the account.
     */
    public function confirm(EmailChanger $emailChanger): RedirectResponse
    {
        request()->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if ($emailChanger->confirm($this->requirePendingUser(), (string) request('code')) === false) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or has expired.'),
            ]);
        }

        Inertia::flash(['message' => 'accounts.email-changed', 'color' => 'success']);

        return to_route('users.account');
    }

    /**
     * Email a fresh code to the pending address.
     */
    public function resend(EmailChanger $emailChanger): Response
    {
        $emailChanger->sendCode($this->requirePendingUser());

        return response()->noContent();
    }

    /**
     * Keep the current address, dropping the pending one.
     */
    public function destroy(EmailChanger $emailChanger): RedirectResponse
    {
        $emailChanger->cancel($this->requireAuthUser());

        Inertia::flash(['message' => 'accounts.email-change-cancelled', 'color' => 'success']);

        return to_route('users.account');
    }

    private function requirePendingUser(): User
    {
        $user = $this->requireAuthUser();

        if ($user->pending_email === null) {
            throw ValidationException::withMessages([
                'code' => __('There is no email address change awaiting confirmation.'),
            ]);
        }

        return $user;
    }
}
