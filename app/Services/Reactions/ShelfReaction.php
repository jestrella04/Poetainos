<?php

namespace App\Services\Reactions;

use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingShelved;
use Illuminate\Notifications\Notification;

class ShelfReaction implements Reaction
{
    public function __construct(private Writing $writing) {}

    public function isActive(User $actor): bool
    {
        return Shelf::where('user_id', $actor->id)->where('writing_id', $this->writing->id)->exists();
    }

    public function add(User $actor): void
    {
        Shelf::create([
            'writing_id' => $this->writing->id,
            'user_id' => $actor->id,
        ]);
    }

    public function remove(User $actor): void
    {
        $actor->shelf()->detach($this->writing->id);
    }

    public function count(): int
    {
        return Shelf::where('writing_id', $this->writing->id)->count();
    }

    public function writing(): ?Writing
    {
        return $this->writing;
    }

    public function author(): ?User
    {
        return $this->writing->author;
    }

    public function notification(User $actor): Notification
    {
        return new WritingShelved($this->writing, $actor);
    }
}
