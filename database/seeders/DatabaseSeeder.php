<?php

namespace Database\Seeders;

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

        factory(App\User::class, $count)->create()->each(function ($user) {
            $user->writings()->save(factory(App\Writing::class)->make());
            $user->votes()->save(factory(App\Vote::class)->make());
            $user->comments()->save(factory(App\Comment::class)->make());
            $user->replies()->save(factory(App\Reply::class)->make());
        });

        factory(App\Writing::class, $count)->create()->each(function ($writing) {
            $writing->categories()->save(factory(App\Category::class)->make());
            $writing->tags()->save(factory(App\Tag::class)->make());
        });

        //factory(App\Hood::class, $count)->make();
        //factory(App\Shelf::class, $count)->make();
    }
}
