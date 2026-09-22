<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => prepareTestEnvironment())
    ->in('Feature');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => prepareTestEnvironment())
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Factories copy demo avatars and covers onto the local disk, so fake it to
 * keep test runs from filling the real storage folders.
 */
function prepareTestEnvironment(): void
{
    Storage::fake('local');
    seedSiteConfig();
}

/**
 * EnsureSiteIsConfigured loads the `poetainos` config from the `settings`
 * table only when it isn't already set. Seed the values controllers/models
 * read via getSiteConfig() directly so requests don't need a `site` row.
 */
function seedSiteConfig(): void
{
    config(['poetainos' => [
        'name' => fakeTitle(),
        'slogan' => fake()->sentence(),
        'pagination' => 10,
        'uploads_max_file_size' => 2048,
        'social' => [],
        'stores' => [],
        'complaints' => [
            ['value' => 'spam', 'label' => fake()->sentence(3)],
            ['value' => 'abuse', 'label' => fake()->sentence(3)],
        ],
        'emails' => ['admin' => fake()->safeEmail()],
        'aura' => [
            'min_at_home' => 90,
            'points' => [
                'user' => [
                    'writing' => 10,
                    'like' => 2,
                    'comment' => 3,
                    'shelf' => 4,
                    'views' => 1,
                    'award' => 20,
                ],
                'writing' => [
                    'like' => 2,
                    'comment' => 3,
                    'shelf' => 4,
                    'views' => 1,
                ],
            ],
        ],
    ]]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createUser(array $attributes = []): User
{
    return User::factory()->create($attributes);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createUserWithPassword(string $password, array $attributes = []): User
{
    return createUser(array_merge($attributes, ['password' => Hash::make($password)]));
}

/**
 * Faker's userName() may contain dots, which neither the registration rule nor
 * the @mention parser accept, so build one from the characters both allow.
 */
function fakeUsername(): string
{
    return fake()->unique()->regexify('[a-z][a-z0-9_]{7,14}');
}

/**
 * Satisfies the registration password rule: 8+ characters with an upper case
 * letter, a lower case letter and a digit.
 */
function fakeStrongPassword(): string
{
    return fake()->regexify('[A-Z][a-z]{6,10}[0-9]{2,4}');
}

/**
 * A short, punctuation-free title, so it survives slugging, HTML escaping and
 * the 3 to 40 character rules of titles and names unchanged.
 */
function fakeTitle(): string
{
    return Str::title(fake()->unique()->words(2, true));
}

/**
 * Prose that clears a minimum length rule while staying well below any maximum.
 */
function fakeText(int $minLength): string
{
    $text = fake()->paragraph();

    while (mb_strlen($text) < $minLength) {
        $text .= ' '.fake()->paragraph();
    }

    return $text;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function actingAsAdmin(array $attributes = []): User
{
    $role = Role::factory()->admin()->create();

    return User::factory()->create(
        fn (array $factoryAttributes): array => array_merge($factoryAttributes, ['role_id' => $role->id], $attributes)
    );
}

/**
 * @param  array<string, mixed>  $data
 */
function createDatabaseNotification(User $recipient, array $data, ?Carbon $createdAt = null): void
{
    $createdAt ??= now();

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\WritingLiked',
        'notifiable_type' => User::class,
        'notifiable_id' => $recipient->id,
        'data' => json_encode($data),
        'read_at' => null,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
}
