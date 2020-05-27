<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Tag;
use App\TagWriting;
use App\Writing;
use Faker\Generator as Faker;

$factory->define(TagWriting::class, function (Faker $faker) {
    return [
        'writing_id' => factory(Writing::class),
        'tag_id' => factory(Tag::class),
    ];
});
