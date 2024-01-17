<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
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

    public function path()
    {
        return route('tags.show', $this->slug);
    }

    public function writings()
    {
        return $this->belongsToMany(Writing::class);
    }
}
