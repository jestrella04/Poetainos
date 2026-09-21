<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Notifications\CategoryFeaturedRandom;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class PostRandomFeaturedCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'category:random';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post a random featured category on social media';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $category = Category::has('writings', '>', 0)->inRandomOrder()->firstOrFail();
        Notification::route('twitter', '')->notify(new CategoryFeaturedRandom($category));

        return self::SUCCESS;
    }
}
