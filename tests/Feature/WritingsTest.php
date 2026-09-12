<?php

use App\Models\Category;
use App\Models\Writing;
use App\Notifications\WritingPublished;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders the index for each sort option', function (string $sort): void {
    Writing::factory()->count(3)->create();

    get('/?sort='.$sort)->assertOk();
})->with(['latest', 'popular', 'likes']);

test('awards page can be rendered', function (): void {
    get(route('writings.awards'))->assertOk();
});

test('show increments views and calculates a finite aura', function (): void {
    $writing = Writing::factory()->create();

    get($writing->path())->assertOk();

    $writing->refresh();
    expect($writing->views)->toBe(1);
    expect(is_finite($writing->aura))->toBeTrue();
});

test('updateAura does not throw when all writing aura points are zeroed', function (): void {
    config(['writerhood.aura.points.writing' => [
        'like' => 0,
        'comment' => 0,
        'shelf' => 0,
        'views' => 0,
    ]]);

    $writing = Writing::factory()->create(['aura' => '1.23']);

    $writing->updateAura();

    expect((float) $writing->refresh()->aura)->toBe(1.23);
});

test('the daily post limit is configurable via site settings', function (): void {
    config(['writerhood.writings' => ['daily_post_limit' => 1]]);

    $user = createUser();
    Writing::factory()->for($user, 'author')->create();

    actingAs($user)
        ->post(route('writings.store'), ['title' => 'One too many'])
        ->assertSessionHasErrors('title');
});

test('random redirects to an existing writing', function (): void {
    $writing = Writing::factory()->create();

    get('/writings/random')->assertRedirect($writing->path());
});

test('random 404s when there are no writings', function (): void {
    get('/writings/random')->assertNotFound();
});

test('guests are redirected away from writings create', function (): void {
    get('/writings/create')->assertRedirect(route('verification.notice'));
});

test('verified users can publish a writing', function (): void {
    Notification::fake();

    $user = createUser();
    $mainCategory = Category::factory()->create(['parent_id' => null]);
    $subCategory = Category::factory()->create(['parent_id' => $mainCategory->id]);

    actingAs($user)->post('/writings/create', [
        'title' => 'My new poem',
        'main_category' => $mainCategory->id,
        'categories' => [$subCategory->id],
        'text' => 'A sufficiently long body of text for validation purposes.',
    ])->assertOk();

    $writing = Writing::where('title', 'My new poem')->firstOrFail();
    expect($writing->categories()->pluck('categories.id')->all())
        ->toContain($mainCategory->id, $subCategory->id);

    Notification::assertSentTo($user, WritingPublished::class);
});

test('users cannot publish more than 3 writings a day', function (): void {
    $user = createUser();
    $mainCategory = Category::factory()->create(['parent_id' => null]);

    Writing::factory()->for($user, 'author')->count(3)->create(['created_at' => now()]);

    actingAs($user)->post('/writings/create', [
        'title' => 'One too many',
        'main_category' => $mainCategory->id,
        'categories' => [$mainCategory->id],
        'text' => 'A sufficiently long body of text for validation purposes.',
    ])->assertSessionHasErrors('title');
});

test('the author can edit and delete their own writing', function (): void {
    $author = createUser();
    $writing = Writing::factory()->for($author, 'author')->create();

    actingAs($author)->get('/writings/edit/'.$writing->slug)->assertOk();
    actingAs($author)->delete('/writings/delete/'.$writing->slug)->assertOk();

    expect(Writing::find($writing->id))->toBeNull();
});

test('a different verified user cannot edit or delete someone else\'s writing', function (): void {
    $writing = Writing::factory()->create();
    $other = createUser();

    actingAs($other)->get('/writings/edit/'.$writing->slug)->assertForbidden();
    actingAs($other)->delete('/writings/delete/'.$writing->slug)->assertForbidden();
});

test('an admin can edit and delete any writing', function (): void {
    $writing = Writing::factory()->create();
    $admin = actingAsAdmin();

    actingAs($admin)->get('/writings/edit/'.$writing->slug)->assertOk();
    actingAs($admin)->delete('/writings/delete/'.$writing->slug)->assertOk();

    expect(Writing::find($writing->id))->toBeNull();
});
