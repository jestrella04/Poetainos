<?php

use App\Models\BlockedUser;
use App\Models\DailySelection;
use App\Models\Writing;
use App\Notifications\WritingOfTheDayPosted;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\get;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

describe('picking the writing of the day', function (): void {
    it('creates a single pick for today and keeps it on later calls', function (): void {
        // Given
        Writing::factory()->count(3)->create();

        // When
        $first = DailySelection::pickForToday();
        $second = DailySelection::pickForToday();

        // Then
        expect($second->is($first))->toBeTrue();
        expect(DailySelection::count())->toBe(1);
        expect($first->selected_on->isToday())->toBeTrue();
    });

    it('skips writings featured within the last 30 days', function (): void {
        // Given
        $recentlyFeatured = Writing::factory()->create();
        $fresh = Writing::factory()->create();
        DailySelection::factory()->create([
            'writing_id' => $recentlyFeatured->id,
            'selected_on' => Carbon::today()->subDays(fake()->numberBetween(1, 29)),
        ]);

        // When
        $pick = DailySelection::pickForToday();

        // Then
        expect($pick->writing_id)->toBe($fresh->id);
    });

    it('allows writings featured more than 30 days ago', function (): void {
        // Given
        $writing = Writing::factory()->create();
        DailySelection::factory()->create([
            'writing_id' => $writing->id,
            'selected_on' => Carbon::today()->subDays(fake()->numberBetween(31, 365)),
        ]);

        // When
        $pick = DailySelection::pickForToday();

        // Then
        expect($pick->writing_id)->toBe($writing->id);
    });

    it('falls back to every writing when all were featured recently', function (): void {
        // Given
        $writing = Writing::factory()->create();
        DailySelection::factory()->create([
            'writing_id' => $writing->id,
            'selected_on' => Carbon::today()->subDay(),
        ]);

        // When
        $pick = DailySelection::pickForToday();

        // Then
        expect($pick->writing_id)->toBe($writing->id);
    });
});

describe('the current pick', function (): void {
    it('is the most recent pick up to today', function (): void {
        // Given
        DailySelection::factory()->create(['selected_on' => Carbon::today()->subDays(fake()->numberBetween(2, 30))]);
        $latest = DailySelection::factory()->create(['selected_on' => Carbon::today()->subDay()]);

        // When
        $current = DailySelection::current();

        // Then
        expect($current?->is($latest))->toBeTrue();
    });

    it('is null when nothing was picked yet', function (): void {
        expect(DailySelection::current())->toBeNull();
    });

    it('falls back to the previous pick when the featured writing is deleted', function (): void {
        // Given
        $previous = DailySelection::factory()->create(['selected_on' => Carbon::today()->subDay()]);
        $featured = Writing::factory()->create();
        DailySelection::factory()->for($featured)->create(['selected_on' => Carbon::today()]);

        // When
        $featured->delete();

        // Then
        expect(DailySelection::current()?->is($previous))->toBeTrue();
    });
});

describe('the midnight command', function (): void {
    it('persists today\'s pick without sending any notification', function (): void {
        // Given
        Notification::fake();
        Writing::factory()->create();

        // When
        pendingArtisan('writing:pick-of-the-day')->assertSuccessful();

        // Then
        expect(DailySelection::whereDate('selected_on', Carbon::today())->count())->toBe(1);
        Notification::assertNothingSent();
    });
});

describe('the notification command', function (): void {
    it('notifies about the persisted pick without creating another one', function (): void {
        // Given
        Notification::fake();
        Writing::factory()->count(2)->create();
        $featuredAuthor = DailySelection::pickForToday()->writing()->firstOrFail()->author()->firstOrFail();

        // When
        pendingArtisan('writing:post-of-the-day')->assertSuccessful();

        // Then
        expect(DailySelection::count())->toBe(1);
        Notification::assertSentTo($featuredAuthor, WritingOfTheDayPosted::class);
        Notification::assertSentTimes(WritingOfTheDayPosted::class, 1);
    });

    it('creates today\'s pick when the midnight command has not run yet', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();

        // When
        pendingArtisan('writing:post-of-the-day')->assertSuccessful();

        // Then
        expect(DailySelection::current()?->writing_id)->toBe($writing->id);
        Notification::assertSentTo($writing->author, WritingOfTheDayPosted::class);
    });
});

describe('the homepage hero', function (): void {
    it('shows the featured writing with its listing relations', function (): void {
        // Given
        $selection = DailySelection::factory()->create(['selected_on' => Carbon::today()]);

        // When
        $writing = $selection->visibleWriting([0]);

        // Then
        expect($writing?->is($selection->writing))->toBeTrue();
        expect($writing?->relationLoaded('author'))->toBeTrue();
        expect($writing?->likes_count)->toBe(0);
    });

    it('hides the featured writing when the viewer blocked its author', function (): void {
        // Given
        $author = createUser();
        $selection = DailySelection::factory()
            ->for(Writing::factory()->for($author, 'author'))
            ->create(['selected_on' => Carbon::today()]);
        $viewer = createUser();
        BlockedUser::factory()->create([
            'user_id' => $viewer->id,
            'blocked_user_id' => $author->id,
        ]);

        // When
        $writing = $selection->visibleWriting($viewer->blockedAuthors()->pluck('blocked_user_id')->all());

        // Then
        expect($writing)->toBeNull();
    });

    it('is not set on other writings listings', function (): void {
        // Given
        DailySelection::factory()->create(['selected_on' => Carbon::today()]);

        // When
        $response = get(route('writings.awards'));

        // Then
        $response->assertInertia(fn ($page) => $page->missing('pickOfTheDay'));
    });
});

describe('the schedule', function (): void {
    it('picks at midnight and notifies at 13:00', function (): void {
        // Given
        $cronFor = fn (string $command): ?string => collect(app(Schedule::class)->events())
            ->first(fn ($event): bool => str_contains((string) $event->command, $command))
            ?->expression;

        // Then
        expect($cronFor('writing:pick-of-the-day'))->toBe('0 0 * * *');
        expect($cronFor('writing:post-of-the-day'))->toBe('0 13 * * *');
    });
});
