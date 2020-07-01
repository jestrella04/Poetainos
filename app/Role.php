<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Role extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'extra_info' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->extra_info['permissions'];
    }

    public function isAllowed($task)
    {
        $this->task = $task;

        $allowed = Arr::first($this->permissions(), function ($value, $key) {
            return $this->task === $value['permission']['name'];
        })['permission'];

        if ($allowed['enabled']) {
            return true;
        } else {
            return false;
        }
    }
}
