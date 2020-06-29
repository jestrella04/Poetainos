<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Comment;
use App\Reply;
use App\User;
use Faker\Generator as Faker;

$factory->define(Reply::class, function (Faker $faker) {
    return [
        'comment_id' => factory(Comment::class),
        'user_id' => factory(User::class),
        'message' => $faker->paragraph
    ];
});
