<?php

/** @var Factory $factory */

use App\CategoryWriting;
use App\Models\Category;
use App\Models\Writing;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(CategoryWriting::class, function (Faker $faker) {
    return [
        'writing_id' => factory(Writing::class),
        'category_id' => factory(Category::class),
    ];
});
