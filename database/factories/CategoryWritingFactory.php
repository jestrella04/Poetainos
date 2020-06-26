<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Category;
use App\CategoryWriting;
use App\Writing;
use Faker\Generator as Faker;

$factory->define(CategoryWriting::class, function (Faker $faker) {
    return [
        'writing_id' => factory(Writing::class),
        'category_id' => factory(Category::class),
    ];
});
