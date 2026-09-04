<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'likeable_type',
        'likeable_id',
        'user_id',
        'vote',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The "booted" method of the model.
     *
     * The `created_at` column has no database default, and $timestamps is
     * false (there is no `updated_at` column), so it must be set explicitly.
     */
    protected static function booted(): void
    {
        static::creating(function (self $like): void {
            $like->created_at ??= now();
        });
    }

    /**
     * Get the parent likeable model (writing, user or comment).
     */
    public function likeable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function writing()
    {
        return $this->belongsTo(Writing::class);
    }
}
