<?php

/** @var Factory $factory */

use App\Models\Tag;
use App\Models\TagWriting;
use App\Models\Writing;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(TagWriting::class, function (Faker $faker) {
    return [
        'writing_id' => factory(Writing::class),
        'tag_id' => factory(Tag::class),
    ];
});
