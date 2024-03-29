<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryWriting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'category_writing';

     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
