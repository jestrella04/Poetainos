<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'reasons' => 'array',
    ];

    /**
     * Get the parent complainable model (writing, user or comment).
     */
    public function complainable()
    {
        return $this->morphTo();
    }
}
