<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'writing_id', 'vote',
    ];

     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function writing()
    {
        return $this->belongsTo(Writing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
