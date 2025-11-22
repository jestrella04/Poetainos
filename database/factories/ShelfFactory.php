<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;
use Faker\Generator as Faker;

$factory->define(Shelf::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'writing_id' => factory((Writing::class))
    ];
});
