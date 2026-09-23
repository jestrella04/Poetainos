<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified using the code
     * that was emailed to them.
     *
     * @return array{url: string}
     */
    public function __invoke(Request $request): array
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->requireAuthUser();

        if ($user->hasVerifiedEmail() === false) {
            if ($user->verifyEmailWithCode($request->input('code')) === false) {
                throw ValidationException::withMessages([
                    'code' => __('The verification code is invalid or has expired.'),
                ]);
            }

            event(new Verified($user));
        }

        return ['url' => route('home')];
    }
}
