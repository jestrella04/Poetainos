<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap';

    /**
     * Routes without parameters that belong in the sitemap.
     *
     * @var array<int, string>
     */
    private const STATIC_ROUTES = ['home', 'explore', 'writings.awards', 'users.index', 'pages.index', 'contact.create'];

    /**
     * Execute the console command.
     *
     * The sitemap is built from the database rather than by crawling the
     * site, because every crawled page would count as a view.
     */
    public function handle(): int
    {
        $sitemap = Sitemap::create();

        foreach (self::STATIC_ROUTES as $routeName) {
            $sitemap->add(Url::create(route($routeName)));
        }

        Page::query()->each(fn (Page $page) => $sitemap->add(Url::create($page->path())));
        Category::query()->each(fn (Category $category) => $sitemap->add(Url::create($category->path())));
        Tag::has('writings')->each(fn (Tag $tag) => $sitemap->add(Url::create($tag->path())));
        User::has('writings')->each(fn (User $user) => $sitemap->add(Url::create($user->path())));
        Writing::query()->each(fn (Writing $writing) => $sitemap->add(
            Url::create($writing->path())->setLastModificationDate($writing->updated_at ?? $writing->created_at ?? Carbon::now())
        ));

        $sitemap->writeToFile(public_path('sitemap.xml'));

        return self::SUCCESS;
    }
}
