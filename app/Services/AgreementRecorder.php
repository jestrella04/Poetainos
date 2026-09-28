<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * The service and privacy agreements a form asks for until the user accepts
 * them, and remembering that they did.
 */
class AgreementRecorder
{
    /**
     * The validation rules of the agreements. Forms post unchecked agreements
     * even when the user already accepted them, so those are only required
     * until then, and never when nobody is agreeing (an admin editing someone
     * else's content).
     *
     * @return array<string, string>
     */
    public function rules(?User $agreeingUser): array
    {
        return $agreeingUser === null || $agreeingUser->isInAgreement() ? [] : [
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ];
    }

    /**
     * Persist the agreements the request accepted, so they aren't asked again.
     */
    public function remember(Request $request, ?User $agreeingUser): void
    {
        if (isTruthy($request->input('service_agreement')) && isTruthy($request->input('privacy_agreement'))) {
            $agreeingUser?->acceptAgreements();
        }
    }
}
