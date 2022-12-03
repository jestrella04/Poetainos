<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Vote;
use App\Models\Writing;
use Faker\Generator as Faker;

$factory->define(Vote::class, function (Faker $faker) {
    return [
        'writing_id' => factory(Writing::class),
        'user_id' => factory(User::class),
        'vote' => $faker->boolean(68)
    ];
});
