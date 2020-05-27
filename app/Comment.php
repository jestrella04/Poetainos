<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'writing_id', 'message',
    ];

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function writing()
    {
        return $this->belongsTo(Writing::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
