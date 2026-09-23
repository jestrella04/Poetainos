<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Console\Scheduling\Schedule;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\flushSession;
use function Pest\Laravel\get;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

describe('counting views', function (): void {
    it('counts a writing once per visitor however often they reload it', function (): void {
        // Given
        $writing = Writing::factory()->create(['views' => 0]);

        // When
        get($writing->path());
        get($writing->path());
        get($writing->path());

        // Then
        expect($writing->refresh()->views)->toBe(1);
    });

    it('counts each visitor separately', function (): void {
        // Given
        $writing = Writing::factory()->create(['views' => 0]);

        // When
        get($writing->path());
        flushSession();
        get($writing->path());

        // Then
        expect($writing->refresh()->views)->toBe(2);
    });

    it('counts different writings separately for the same visitor', function (): void {
        // Given
        $first = Writing::factory()->create(['views' => 0]);
        $second = Writing::factory()->create(['views' => 0]);

        // When
        get($first->path());
        get($second->path());

        // Then
        expect($first->refresh()->views)->toBe(1);
        expect($second->refresh()->views)->toBe(1);
    });

    it('shows the view that was just counted', function (): void {
        // Given
        $views = fake()->numberBetween(0, 1000);
        $writing = Writing::factory()->create(['views' => $views]);

        // When
        $response = get($writing->path());

        // Then
        $response->assertInertia(fn ($page) => $page->where('writing.views', $views + 1));
    });

    it('counts a profile once per visitor', function (): void {
        // Given
        $user = createUser();
        DB::table('users')->where('id', $user->id)->update(['profile_views' => 0]);

        // When
        get($user->path());
        get($user->path());

        // Then
        expect($user->refresh()->profile_views)->toBe(1);
    });
});

describe('viewing a page', function (): void {
    it('does not recalculate any aura', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['views' => 0, 'aura' => 0]);

        // When
        get($writing->path());
        get($author->path());

        // Then
        expect($writing->refresh()->aura_updated_at)->toBeNull();
        expect($author->refresh()->aura_updated_at)->toBeNull();
    });
});

describe('the aura:update command', function (): void {
    it('recalculates the aura of writings and users from their views', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['views' => fake()->numberBetween(1, 1000), 'aura' => 0]);
        DB::table('users')->where('id', $author->id)->update(['profile_views' => fake()->numberBetween(1, 1000), 'aura' => 0]);

        // When
        pendingArtisan('aura:update')->assertSuccessful();

        // Then
        expect((float) $writing->refresh()->aura)->toBeGreaterThan(0.0);
        expect((float) $author->refresh()->aura)->toBeGreaterThan(0.0);
    });

    it('is scheduled daily', function (): void {
        // When
        $events = collect(app(Schedule::class)->events());

        // Then
        expect($events->contains(fn ($event) => str_contains((string) $event->command, 'aura:update')))->toBeTrue();
    });
});

describe('the related writings', function (): void {
    it('leave out writings by authors the viewer blocked', function (): void {
        // Given
        $viewer = createUser();
        $blocked = createUser();
        $viewer->block($blocked);
        $category = Category::factory()->create(['parent_id' => null]);
        $current = Writing::factory()->create();
        $current->categories()->attach($category);
        $visible = Writing::factory()->create();
        $visible->categories()->attach($category);
        $hidden = Writing::factory()->for($blocked, 'author')->create();
        $hidden->categories()->attach($category);

        // When
        $response = actingAs($viewer)->get($current->path());

        // Then
        $response->assertInertia(fn ($page) => $page
            ->has('related.from_category', 1)
            ->where('related.from_category.0.id', $visible->id));
    });

    it('never include the writing being read', function (): void {
        // Given
        $category = Category::factory()->create(['parent_id' => null]);
        $current = Writing::factory()->create();
        $current->categories()->attach($category);

        // When
        $response = get($current->path());

        // Then
        $response->assertInertia(fn ($page) => $page->has('related.from_category', 0));
    });

    it('leave out blocked authors on a profile\'s shelf and liked lists', function (): void {
        // Given
        $viewer = createUser();
        $blocked = createUser();
        $viewer->block($blocked);
        $profile = createUser();
        $visible = Writing::factory()->create();
        $hidden = Writing::factory()->for($blocked, 'author')->create();
        $profile->shelf()->attach([$visible->id, $hidden->id]);
        foreach ([$visible, $hidden] as $writing) {
            $writing->likes()->create(['user_id' => $profile->id, 'vote' => 1]);
        }

        // When
        $response = actingAs($viewer)->get($profile->path());

        // Then
        $response->assertInertia(fn ($page) => $page
            ->has('writings.from_shelf', 1)
            ->where('writings.from_shelf.0.id', $visible->id)
            ->has('writings.from_liked', 1)
            ->where('writings.from_liked.0.id', $visible->id));
    });

    it('show at most five likers', function (): void {
        // Given
        $writing = Writing::factory()->create();
        User::factory()->count(fake()->numberBetween(6, 10))->create()
            ->each(fn (User $liker) => $writing->likes()->create(['user_id' => $liker->id, 'vote' => 1]));

        // When
        $response = get($writing->path());

        // Then
        $response->assertInertia(fn ($page) => $page->has('likers', 5));
    });
});
