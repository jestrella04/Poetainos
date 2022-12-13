<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent likeable model (writing, user or comment).
     */
    public function likeable()
    {
        return $this->morphTo();
    }
}
