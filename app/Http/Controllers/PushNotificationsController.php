<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushNotificationsController extends Controller
{
    /**
     * Update user's subscription.
     */
    public function update(Request $request): JsonResponse
    {
        $this->validate($request, ['endpoint' => 'required']);

        $request->user()?->updatePushSubscription(
            $request->endpoint,
            $request->publicKey,
            $request->authToken,
            $request->contentEncoding
        );

        return response()->json(null, 204);
    }

    /**
     * Delete the specified subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->validate($request, ['endpoint' => 'required']);

        $request->user()?->deletePushSubscription($request->endpoint);

        return response()->json(null, 204);
    }
}
