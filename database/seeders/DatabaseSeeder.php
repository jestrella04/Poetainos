<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Shelf;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    private const int USERS_COUNT = 15;

    private const int WRITINGS_COUNT = 30;

    private const int TAGS_COUNT = 10;

    private const array VIEWS_RANGES = [
        [0, 10],
        [500, 900],
        [3000, 4000],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $users = User::factory(self::USERS_COUNT)->create();
        $tags = Tag::factory(self::TAGS_COUNT)->create();
        $mainCategories = Category::whereNull('parent_id')->with('children')->get();
        $writings = $this->seedWritings($users, $tags, $mainCategories);

        $this->seedComments($writings, $users);
        $this->seedEngagement($writings, $users);
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Tag>  $tags
     * @param  Collection<int, Category>  $mainCategories
     * @return Collection<int, Writing>
     */
    private function seedWritings(Collection $users, Collection $tags, Collection $mainCategories): Collection
    {
        return collect(range(1, self::WRITINGS_COUNT))->map(function () use ($users, $tags, $mainCategories) {
            $mainCategory = $mainCategories->random();
            $subCategories = $mainCategory->children->random(random_int(0, 2));
            $writing = Writing::factory()->create([
                'user_id' => $users->random()->id,
                'views' => $this->randomViewsCount(),
            ]);

            $writing->categories()->attach(
                collect([$mainCategory->id])->merge($subCategories->pluck('id'))
            );

            $writing->tags()->attach($tags->random(random_int(1, 3))->pluck('id'));

            return $writing;
        });
    }

    /**
     * Picks one of the VIEWS_RANGES at random (low, medium or high traffic)
     * and returns a random view count within it.
     */
    private function randomViewsCount(): int
    {
        [$min, $max] = self::VIEWS_RANGES[array_rand(self::VIEWS_RANGES)];

        return random_int($min, $max);
    }

    /**
     * @param  Collection<int, Writing>  $writings
     * @param  Collection<int, User>  $users
     */
    private function seedComments(Collection $writings, Collection $users): void
    {
        foreach ($writings as $writing) {
            Comment::factory(random_int(0, 5))->create([
                'writing_id' => $writing->id,
                'user_id' => fn () => $users->random()->id,
            ]);
        }
    }

    /**
     * Half of the writings get no engagement at all; the other half get
     * between 1 and 5 likes and, separately, between 1 and 5 shelf entries,
     * each from distinct non-author users (to respect the `likes` unique
     * constraint, the `shelves` composite primary key, and the app's
     * no-self-like rule).
     *
     * @param  Collection<int, Writing>  $writings
     * @param  Collection<int, User>  $users
     */
    private function seedEngagement(Collection $writings, Collection $users): void
    {
        $engagedWritings = $writings->shuffle()->take((int) floor($writings->count() / 2));

        foreach ($engagedWritings as $writing) {
            $candidates = $users->reject(fn (User $user) => $user->id === $writing->user_id);

            $likers = $candidates->random(min(random_int(1, 5), $candidates->count()));
            foreach ($likers as $liker) {
                Like::factory()->create([
                    'likeable_type' => Writing::class,
                    'likeable_id' => $writing->id,
                    'user_id' => $liker->id,
                ]);
            }

            $shelvers = $candidates->random(min(random_int(1, 5), $candidates->count()));
            foreach ($shelvers as $sheller) {
                Shelf::factory()->create([
                    'user_id' => $sheller->id,
                    'writing_id' => $writing->id,
                ]);
            }
        }
    }
}
