<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function path(): string
    {
        return route('tags.show', $this->slug);
    }

    /**
     * @return BelongsToMany<Writing, $this>
     */
    public function writings(): BelongsToMany
    {
        return $this->belongsToMany(Writing::class);
    }
}
