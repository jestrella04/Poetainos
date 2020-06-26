<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Writing;
use App\User;
use Faker\Generator as Faker;

$factory->define(Writing::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'title' => $faker->text(45),
        'slug' => $faker->unique()->slug(3),
        'text' => $faker->paragraph
    ];
});
