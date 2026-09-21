<?php

namespace App\Services\Reactions;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Notifications\Notification;

/**
 * Something a user can toggle on a piece of content, like a like or a shelving.
 */
interface Reaction
{
    public function isActive(User $actor): bool;

    /**
     * @throws UniqueConstraintViolationException when the actor already reacted.
     */
    public function add(User $actor): void;

    public function remove(User $actor): void;

    public function count(): int;

    /**
     * The writing whose aura depends on this reaction, if the reacted content is one.
     */
    public function writing(): ?Writing;

    /**
     * Who to tell that the actor reacted.
     */
    public function author(): ?User;

    public function notification(User $actor): Notification;
}
