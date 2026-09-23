<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\Writing;
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

describe('the daily post limit', function (): void {
    it('is configurable via site settings', function (): void {
        // Given
        config(['poetainos.writings' => ['daily_post_limit' => 1]]);
        $user = createUser();
        Writing::factory()->for($user, 'author')->create();

        // When
        $response = actingAs($user)->post(route('writings.store'), ['title' => fakeTitle()]);

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
        $title = fakeTitle();

        // When
        $response = actingAs($user)->post('/writings/create', [
            'title' => $title,
            'main_category' => $mainCategory->id,
            'categories' => [$subCategory->id],
            'text' => fakeText(10),
        ]);

        // Then
        $response->assertOk();
        $writing = Writing::where('title', $title)->firstOrFail();
        expect($writing->categories()->pluck('categories.id')->all())
            ->toContain($mainCategory->id, $subCategory->id);
    });

    it('allows a user who already accepted the agreements to publish when the form submits them unchecked', function (): void {
        // Given
        Notification::fake();
        $user = createUser();
        $user->acceptAgreements();
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        $title = fakeTitle();

        // When
        $response = actingAs($user)->post('/writings/create', writingPayload($mainCategory, [
            'title' => $title,
            'service_agreement' => 'false',
            'privacy_agreement' => 'false',
        ]));

        // Then
        $response->assertOk();
        expect(Writing::where('title', $title)->exists())->toBeTrue();
    });

    it('rejects publishing when a user who has not agreed submits the agreements unchecked', function (): void {
        // Given
        $user = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        $response = actingAs($user)->post('/writings/create', writingPayload($mainCategory, [
            'service_agreement' => 'false',
            'privacy_agreement' => 'false',
        ]));

        // Then
        $response->assertSessionHasErrors(['service_agreement', 'privacy_agreement']);
    });

    it('prevents publishing more than 3 writings a day', function (): void {
        // Given
        $user = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        Writing::factory()->for($user, 'author')->count(3)->create(['created_at' => now()]);

        // When
        $response = actingAs($user)->post('/writings/create', writingPayload($mainCategory));

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

    it('lets an admin delete a writing from the admin area', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->deleteJson(route('admin.writings.destroy', $writing));

        // Then
        $response->assertOk();
        expect(Writing::find($writing->id))->toBeNull();
    });

    it('keeps the admin area delete route closed to the author', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        $response = actingAs($author)->deleteJson(route('admin.writings.destroy', $writing));

        // Then
        $response->assertForbidden();
        expect(Writing::find($writing->id))->not->toBeNull();
    });
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function writingPayload(Category $mainCategory, array $overrides = []): array
{
    return array_merge([
        'title' => fakeTitle(),
        'main_category' => $mainCategory->id,
        'categories' => [$mainCategory->id],
        'text' => fakeText(10),
    ], $overrides);
}

describe('updating a writing', function (): void {
    it('still lets an author edit once the daily post limit is reached', function (): void {
        // Given
        config(['poetainos.writings' => ['daily_post_limit' => 1]]);
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['created_at' => now()]);
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        $title = fakeTitle();

        // When
        $response = actingAs($author)->put(route('writings.update', $writing), writingPayload($mainCategory, [
            'title' => $title,
        ]));

        // Then
        $response->assertOk();
        expect($writing->refresh()->title)->toBe($title);
    });

    it('keeps the slug and the other stored details when editing', function (): void {
        // Given
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $originalSlug = $writing->slug;
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        $link = fake()->url();

        // When
        actingAs($author)->put(route('writings.update', $writing), writingPayload($mainCategory, [
            'link' => $link,
        ]))->assertOk();

        // Then
        expect($writing->refresh()->slug)->toBe($originalSlug);
        expect(data_get($writing->extra_info, 'link'))->toBe($link);
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
        'too many tags' => [fn (): array => array_map(fn (): string => fake()->unique()->word(), range(1, 11))],
        'a tag that is too long' => [fn (): array => [fake()->lexify(str_repeat('?', 41))]],
        'a blank tag' => [['   ']],
    ]);

    it('normalizes whitespace and reuses existing tags', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        [$firstWord, $secondWord, $otherTag] = array_map(fn (): string => fake()->unique()->word(), range(1, 3));
        $spacedTag = "{$firstWord} {$secondWord}";

        // When
        actingAs($author)->post(route('writings.store'), writingPayload($mainCategory, [
            'tags' => ["{$firstWord}   {$secondWord}", $spacedTag, $otherTag],
        ]))->assertOk();

        // Then
        $writing = Writing::firstOrFail();
        expect($writing->tags()->pluck('name')->sort()->values()->all())
            ->toBe(collect([$spacedTag, $otherTag])->sort()->values()->all());
    });

    it('does not leave a half saved writing behind when syncing fails', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        Tag::creating(fn () => throw new RuntimeException(fake()->sentence()));

        // When
        $response = actingAs($author)->post(route('writings.store'), writingPayload($mainCategory, ['tags' => [fake()->word()]]));

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
        $oldCover = 'covers/'.fake()->uuid().'.png';
        Storage::disk('local')->put($oldCover, fake()->sentence());
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['extra_info' => ['cover' => $oldCover]]);
        $mainCategory = Category::factory()->create(['parent_id' => null]);

        // When
        actingAs($author)->post(route('writings.update', $writing), writingPayload($mainCategory, [
            '_method' => 'PUT',
            'cover' => UploadedFile::fake()->image(fake()->word().'.jpg', fake()->numberBetween(1400, 2400), fake()->numberBetween(800, 1400)),
        ]))->assertOk();

        // Then
        $cover = data_get($writing->refresh()->extra_info, 'cover');
        expect($cover)->toStartWith('covers/')->not->toBe($oldCover);
        expect(storedImageWidth($cover))->toBe(1280);
        Storage::disk('local')->assertMissing($oldCover);
    });

    it('is deleted together with the writing', function (): void {
        // Given
        $cover = 'covers/'.fake()->uuid().'.png';
        Storage::disk('local')->put($cover, fake()->sentence());
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create(['extra_info' => ['cover' => $cover]]);

        // When
        actingAs($author)->delete(route('writings.destroy', $writing))->assertOk();

        // Then
        Storage::disk('local')->assertMissing($cover);
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
