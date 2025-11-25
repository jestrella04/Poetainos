<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Writing;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Tag;

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

        User::factory($count)->create()->each(function ($user) {
            $user->writings()->save(Writing::factory()->make());
            //$user->likes()->save(Like::factory()->make());
            $user->comments()->save(Comment::factory()->make());
        });

        Writing::factory($count)->create()->each(function ($writing) {
            $writing->categories()->save(Category::factory()->make());
            $writing->tags()->save(Tag::factory()->make());
        });

        //factory(App\Models\Hood::class, $count)->make();
        //factory(App\Models\Shelf::class, $count)->make();
    }
}
