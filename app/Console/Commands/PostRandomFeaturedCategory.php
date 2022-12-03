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
    protected $description = 'Post a random featured category to Twitter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $category = Category::secondary()->random();
        Notification::route('twitter', '')->notify(new CategoryFeaturedRandom($category));
    }
}
