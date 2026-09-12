<?php

namespace App\Models;

use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reasons',
        'comment',
        'closed_at',
        'closed_comment',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reasons' => 'array',
    ];

    /**
     * Get the parent complainable model (writing, user or comment).
     *
     * @return MorphTo<Model, $this>
     */
    public function complainable(): MorphTo
    {
        return $this->morphTo();
    }
}
