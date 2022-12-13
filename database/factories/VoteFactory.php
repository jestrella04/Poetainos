<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Like;
use App\Models\Writing;
use Faker\Generator as Faker;

$factory->define(Like::class, function (Faker $faker) {
    return [
        'writing_id' => factory(Writing::class),
        'user_id' => factory(User::class),
        'like' => $faker->boolean(68)
    ];
});
