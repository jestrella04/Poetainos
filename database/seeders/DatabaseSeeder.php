<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $count = 10;

        factory(App\Models\User::class, $count)->create()->each(function ($user) {
            $user->writings()->save(factory(App\Models\Writing::class)->make());
            $user->votes()->save(factory(App\Models\Vote::class)->make());
            $user->comments()->save(factory(App\Models\Comment::class)->make());
        });

        factory(App\Models\Writing::class, $count)->create()->each(function ($writing) {
            $writing->categories()->save(factory(App\Models\Category::class)->make());
            $writing->tags()->save(factory(App\Models\Tag::class)->make());
        });

        //factory(App\Models\Hood::class, $count)->make();
        //factory(App\Models\Shelf::class, $count)->make();
    }
}
