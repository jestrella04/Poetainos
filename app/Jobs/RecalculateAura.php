<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Brings the aura of a user and/or a writing up to date after an interaction
 * (a like, a shelving, a comment) so the request that caused it doesn't wait for it.
 */
class RecalculateAura implements ShouldQueue
{
    use Queueable;

    /**
     * A user or writing deleted before the job runs has nothing left to recalculate.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public ?User $user = null, public ?Writing $writing = null) {}

    public function handle(): void
    {
        $this->user?->updateAura();
        $this->writing?->updateAura();
    }
}
