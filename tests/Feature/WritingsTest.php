<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\Writing;
use App\Notifications\WritingPublished;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

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
        config(['poetainos.aura.points.writing' => [
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
        config(['poetainos.writings' => ['daily_post_limit' => 1]]);
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
        $response->assertRedirect(route('login'));
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

    it('allows a user who already accepted the agreements to publish when the form submits them unchecked', function (): void {
        // Given
        Notification::fake();
        $user = createUser();
        $user->acceptAgreements();
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        $response = actingAs($user)->post('/writings/create', [
            'title' => 'Already agreed',
            'main_category' => $mainCategory->id,
            'categories' => [$mainCategory->id],
            'text' => 'A sufficiently long body of text for validation purposes.',
            'service_agreement' => 'false',
            'privacy_agreement' => 'false',
        ]);

        // Then
        $response->assertOk();
        expect(Writing::where('title', 'Already agreed')->exists())->toBeTrue();
    });

    it('rejects publishing when a user who has not agreed submits the agreements unchecked', function (): void {
        // Given
        $user = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        $response = actingAs($user)->post('/writings/create', [
            'title' => 'Not agreed',
            'main_category' => $mainCategory->id,
            'categories' => [$mainCategory->id],
            'text' => 'A sufficiently long body of text for validation purposes.',
            'service_agreement' => 'false',
            'privacy_agreement' => 'false',
        ]);

        // Then
        $response->assertSessionHasErrors(['service_agreement', 'privacy_agreement']);
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

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function writingPayload(Category $mainCategory, array $overrides = []): array
{
    return array_merge([
        'title' => 'A fresh title',
        'main_category' => $mainCategory->id,
        'categories' => [$mainCategory->id],
        'text' => 'A sufficiently long body of text for validation purposes.',
    ], $overrides);
}

describe('updating a writing', function (): void {
    it('still lets an author edit once the daily post limit is reached', function (): void {
        // Given
        config(['poetainos.writings' => ['daily_post_limit' => 1]]);
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['created_at' => now()]);
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        $response = actingAs($author)->put(route('writings.update', $writing), writingPayload($mainCategory, [
            'title' => 'Edited title',
        ]));

        // Then
        $response->assertOk();
        expect($writing->refresh()->title)->toBe('Edited title');
    });

    it('keeps the slug and the other stored details when editing', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['slug' => 'original-slug']);
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        actingAs($author)->put(route('writings.update', $writing), writingPayload($mainCategory, [
            'link' => 'https://example.com/poem',
        ]))->assertOk();

        // Then
        expect($writing->refresh()->slug)->toBe('original-slug');
        expect($writing->extra_info['link'])->toBe('https://example.com/poem');
    });

    it('does not reuse a slug that a static route owns', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        actingAs($author)->post(route('writings.store'), writingPayload($mainCategory, ['title' => 'Random']))->assertOk();

        // Then
        expect(Writing::where('title', 'Random')->firstOrFail()->slug)->toBe('random-1');
    });

    it('rejects a main category that is not a top level category', function (): void {
        // Given
        $author = createUser();
        $parent = Category::factory()->create(['parent_id' => null]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        // When
        $response = actingAs($author)->postJson(route('writings.store'), writingPayload($child));

        // Then
        $response->assertJsonValidationErrors('main_category');
    });

    it('validates the tags', function (array $tags): void {
        // Given
        $author = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        $response = actingAs($author)->postJson(route('writings.store'), writingPayload($mainCategory, ['tags' => $tags]));

        // Then
        $response->assertUnprocessable();
        expect(Writing::count())->toBe(0);
    })->with([
        'too many tags' => [array_map(fn (int $n): string => "tag{$n}", range(1, 11))],
        'a tag that is too long' => [[str_repeat('a', 41)]],
        'a blank tag' => [['   ']],
    ]);

    it('normalizes whitespace and reuses existing tags', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        actingAs($author)->post(route('writings.store'), writingPayload($mainCategory, [
            'tags' => ['free   verse', 'free verse', 'haiku'],
        ]))->assertOk();

        // Then
        $writing = Writing::firstOrFail();
        expect($writing->tags()->pluck('name')->sort()->values()->all())->toBe(['free verse', 'haiku']);
    });

    it('does not leave a half saved writing behind when syncing fails', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        Tag::creating(fn () => throw new RuntimeException('tag failure'));

        // When
        $response = actingAs($author)->post(route('writings.store'), writingPayload($mainCategory, ['tags' => ['boom']]));

        // Then
        $response->assertServerError();
        expect(Writing::count())->toBe(0);
    });
});

describe('a writing cover', function (): void {
    beforeEach(function (): void {
        Storage::fake('local');
    });

    it('is cropped to 16:9 and replaces the previous cover', function (): void {
        // Given
        Storage::disk('local')->put('covers/old.png', 'old');
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['extra_info' => ['cover' => 'covers/old.png']]);
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        actingAs($author)->post(route('writings.update', $writing), writingPayload($mainCategory, [
            '_method' => 'PUT',
            'cover' => UploadedFile::fake()->image('cover.jpg', 2000, 1000),
        ]))->assertOk();

        // Then
        $cover = $writing->refresh()->extra_info['cover'];
        expect($cover)->toStartWith('covers/')->not->toBe('covers/old.png');
        expect(getimagesize(Storage::disk('local')->path($cover))[0])->toBe(1280);
        Storage::disk('local')->assertMissing('covers/old.png');
    });

    it('is deleted together with the writing', function (): void {
        // Given
        Storage::disk('local')->put('covers/mine.png', 'x');
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['extra_info' => ['cover' => 'covers/mine.png']]);

        // When
        actingAs($author)->delete(route('writings.destroy', $writing))->assertOk();

        // Then
        Storage::disk('local')->assertMissing('covers/mine.png');
    });
});

describe('the writing form', function (): void {
    beforeEach(function (): void {
        config(['inertia.testing.ensure_pages_exist' => false]);
    });

    it('tells the page whether it is editing an existing writing', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        $create = actingAs($author)->get(route('writings.create'));
        $edit = actingAs($author)->get(route('writings.edit', $writing));

        // Then
        $create->assertInertia(fn ($page) => $page->where('isUpdate', false));
        $edit->assertInertia(fn ($page) => $page->where('isUpdate', true));
    });
});
