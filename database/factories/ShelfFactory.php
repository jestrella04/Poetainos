<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Shelf;
use App\User;
use App\Writing;
use Faker\Generator as Faker;

$factory->define(Shelf::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'writing_id' => factory((Writing::class))
    ];
});
