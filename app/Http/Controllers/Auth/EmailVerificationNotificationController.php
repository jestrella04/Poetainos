<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): Response
    {
        $user = $request->user();

        // Nothing to send once the address is verified
        if ($user !== null && $user->hasVerifiedEmail() === false) {
            $user->sendEmailVerificationNotification();
        }

        return response()->noContent();
    }
}
