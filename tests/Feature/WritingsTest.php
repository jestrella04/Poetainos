<?php

use App\Models\Category;
use App\Models\Writing;
use App\Notifications\WritingPublished;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('the index page', function (): void {
    it('renders for each sort option', function (string $sort): void {
        // Given
        Writing::factory()->count(3)->create();

        // When
        $response = get('/?sort='.$sort);

        // Then
        $response->assertOk();
    })->with(['latest', 'popular', 'likes']);
});

describe('the awards page', function (): void {
    it('can be rendered', function (): void {
        // When
        $response = get(route('writings.awards'));

        // Then
        $response->assertOk();
    });
});

describe('showing a writing', function (): void {
    it('increments views and calculates a finite aura', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $response = get($writing->path());

        // Then
        $response->assertOk();
        $writing->refresh();
        expect($writing->views)->toBe(1);
        expect(is_finite($writing->aura))->toBeTrue();
    });
});

describe('aura calculation', function (): void {
    it('does not throw when all writing aura points are zeroed', function (): void {
        // Given
        config(['writerhood.aura.points.writing' => [
            'like' => 0,
            'comment' => 0,
            'shelf' => 0,
            'views' => 0,
        ]]);
        $writing = Writing::factory()->create(['aura' => '1.23']);

        // When
        $writing->updateAura();

        // Then
        expect((float) $writing->refresh()->aura)->toBe(1.23);
    });
});

describe('the daily post limit', function (): void {
    it('is configurable via site settings', function (): void {
        // Given
        config(['writerhood.writings' => ['daily_post_limit' => 1]]);
        $user = createUser();
        Writing::factory()->for($user, 'author')->create();

        // When
        $response = actingAs($user)->post(route('writings.store'), ['title' => 'One too many']);

        // Then
        $response->assertSessionHasErrors('title');
    });
});

describe('random writing redirect', function (): void {
    it('redirects to an existing writing', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $response = get('/writings/random');

        // Then
        $response->assertRedirect($writing->path());
    });

    it('404s when there are no writings', function (): void {
        // When
        $response = get('/writings/random');

        // Then
        $response->assertNotFound();
    });
});

describe('creating a writing', function (): void {
    it('redirects guests away from the create page', function (): void {
        // When
        $response = get('/writings/create');

        // Then
        $response->assertRedirect(route('verification.notice'));
    });

    it('allows a verified user to publish a writing', function (): void {
        // Given
        Notification::fake();
        $user = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        $subCategory = Category::factory()->create(['parent_id' => $mainCategory->id]);

        // When
        $response = actingAs($user)->post('/writings/create', [
            'title' => 'My new poem',
            'main_category' => $mainCategory->id,
            'categories' => [$subCategory->id],
            'text' => 'A sufficiently long body of text for validation purposes.',
        ]);

        // Then
        $response->assertOk();
        $writing = Writing::where('title', 'My new poem')->firstOrFail();
        expect($writing->categories()->pluck('categories.id')->all())
            ->toContain($mainCategory->id, $subCategory->id);
        Notification::assertSentTo($user, WritingPublished::class);
    });

    it('prevents publishing more than 3 writings a day', function (): void {
        // Given
        $user = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        Writing::factory()->for($user, 'author')->count(3)->create(['created_at' => now()]);

        // When
        $response = actingAs($user)->post('/writings/create', [
            'title' => 'One too many',
            'main_category' => $mainCategory->id,
            'categories' => [$mainCategory->id],
            'text' => 'A sufficiently long body of text for validation purposes.',
        ]);

        // Then
        $response->assertSessionHasErrors('title');
    });
});

describe('editing and deleting a writing', function (): void {
    it('allows the author to edit and delete their own writing', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        $editResponse = actingAs($author)->get('/writings/edit/'.$writing->slug);
        $deleteResponse = actingAs($author)->delete('/writings/delete/'.$writing->slug);

        // Then
        $editResponse->assertOk();
        $deleteResponse->assertOk();
        expect(Writing::find($writing->id))->toBeNull();
    });

    it('forbids a different verified user from editing or deleting someone else\'s writing', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $other = createUser();

        // When
        $editResponse = actingAs($other)->get('/writings/edit/'.$writing->slug);
        $deleteResponse = actingAs($other)->delete('/writings/delete/'.$writing->slug);

        // Then
        $editResponse->assertForbidden();
        $deleteResponse->assertForbidden();
    });

    it('allows an admin to edit and delete any writing', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $admin = actingAsAdmin();

        // When
        $editResponse = actingAs($admin)->get('/writings/edit/'.$writing->slug);
        $deleteResponse = actingAs($admin)->delete('/writings/delete/'.$writing->slug);

        // Then
        $editResponse->assertOk();
        $deleteResponse->assertOk();
        expect(Writing::find($writing->id))->toBeNull();
    });
});
