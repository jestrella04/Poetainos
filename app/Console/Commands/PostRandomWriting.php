<?php

namespace App\Console\Commands;

use App\Notifications\WritingRandom;
use App\User;
use Illuminate\Console\Command;

class PostRandomWriting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'writing:random';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post a random writing to Twitter';

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
        $writing = User::has('writings', '>', 0)
            ->inRandomOrder()
            ->firstOrFail()
            ->writings()
            ->inRandomOrder()
            ->firstOrFail();
        $writing->author->notify(new WritingRandom($writing));
    }
}
