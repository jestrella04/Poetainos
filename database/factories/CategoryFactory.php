<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' =>$faker->unique()->word,
        'slug' =>$faker->unique()->slug(3),
        'description' => $faker->optional()->paragraph
    ];
});
