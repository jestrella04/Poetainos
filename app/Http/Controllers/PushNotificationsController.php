<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Minishlink\WebPush\ContentEncoding;

class PushNotificationsController extends Controller
{
    /**
     * Update user's subscription.
     */
    public function update(Request $request): Response
    {
        $request->validate([
            'endpoint' => 'required|url|max:500',
            'publicKey' => 'nullable|string|max:255',
            'authToken' => 'nullable|string|max:255',
            'contentEncoding' => ['nullable', Rule::enum(ContentEncoding::class)],
        ]);

        $this->requireAuthUser()->updatePushSubscription(
            request('endpoint'),
            request('publicKey'),
            request('authToken'),
            request('contentEncoding'),
        );

        return response()->noContent();
    }

    /**
     * Delete the specified subscription.
     */
    public function destroy(Request $request): Response
    {
        $request->validate([
            'endpoint' => 'required|url|max:500',
        ]);

        $this->requireAuthUser()->deletePushSubscription(request('endpoint'));

        return response()->noContent();
    }
}
