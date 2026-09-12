<?php

namespace App\Models;

use Database\Factories\BlockedUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    /** @use HasFactory<BlockedUserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'blocked_user_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blocked_users';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
