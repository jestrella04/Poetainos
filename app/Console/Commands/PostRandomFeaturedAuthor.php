<?php

namespace App\Console\Commands;

use App\Notifications\AuthorFeaturedRandom;
use App\User;
use Illuminate\Console\Command;

class PostRandomFeaturedAuthor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'author:random';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post a random featured author to Twitter';

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
        $user = User::where('aura', '>', 0)
            ->orderBy('aura', 'desc')
            ->take(50)
            ->get()
            ->random();
        $user->notify(new AuthorFeaturedRandom($user));
    }
}
