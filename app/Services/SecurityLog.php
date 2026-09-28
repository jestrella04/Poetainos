<?php

namespace App\Services;

use App\Models\User;

/**
 * An audit trail of privileged actions, written to the `security` log channel,
 * which the admin panel can read but not clear.
 */
class SecurityLog
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $event, ?User $actor, array $context = []): void
    {
        logger()->channel('security')->info($event, [
            'actor_id' => $actor?->id,
            'ip' => request()->ip(),
            ...$context,
        ]);
    }
}
