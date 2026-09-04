<?php

/** @var Factory $factory */

use App\Models\Hood;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Hood::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'fellow_user_id' => factory(User::class),
    ];
});
