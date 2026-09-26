<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Writing;
use App\Services\AuraCalculator;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Brings the aura of a user and/or a writing up to date after an interaction
 * (a like, a shelving, a comment) so the request that caused it doesn't wait for it.
 * A burst of interactions queues it once: it reads the counts when it runs, so
 * one pending recalculation covers them all. The lock is released as it starts,
 * so an interaction arriving mid-run still queues a fresh one.
 */
class RecalculateAura implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    /**
     * A user or writing deleted before the job runs has nothing left to recalculate.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Seconds after which a pending job no longer blocks an identical one, should it never run.
     */
    public int $uniqueFor = 60;

    public function __construct(public ?User $user = null, public ?Writing $writing = null) {}

    public function uniqueId(): string
    {
        return ($this->user->id ?? '-').':'.($this->writing->id ?? '-');
    }

    public function handle(AuraCalculator $calculator): void
    {
        if ($this->user !== null) {
            $calculator->updateUserAura($this->user);
        }

        if ($this->writing !== null) {
            $calculator->updateWritingAura($this->writing);
        }
    }
}
