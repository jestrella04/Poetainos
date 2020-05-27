<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Category;
use App\Type;
use App\Writing;
use App\User;
use Faker\Generator as Faker;

$factory->define(Writing::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'category_id' =>  factory(Category::class),
        'type_id' => factory(Type::class),
        'title' => $faker->text(45),
        'slug' => $faker->unique()->slug(3),
        'text' => $faker->paragraph
    ];
});
