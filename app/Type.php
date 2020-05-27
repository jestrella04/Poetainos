<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public function path()
    {
        return route('types.show', $this->slug);
    }

    public function writings()
    {
        return $this->hasMany(Writing::class);
    }
}
