<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingPublished;
use Illuminate\Support\Facades\Notification;

test('index renders for each sort option', function (string $sort): void {
    Writing::factory()->count(3)->create();

    $this->get('/?sort='.$sort)->assertOk();
})->with(['latest', 'popular', 'likes']);

test('awards page can be rendered', function (): void {
    $this->get(route('writings.awards'))->assertOk();
});

test('show increments views and calculates a finite aura', function (): void {
    $writing = Writing::factory()->create();

    $this->get($writing->path())->assertOk();

    $fresh = $writing->fresh();
    expect($fresh->views)->toBe(1);
    expect(is_numeric($fresh->aura))->toBeTrue();
});

test('random redirects to an existing writing', function (): void {
    $writing = Writing::factory()->create();

    $this->get('/writings/random')->assertRedirect($writing->path());
});

test('random 404s when there are no writings', function (): void {
    $this->get('/writings/random')->assertNotFound();
});

test('guests are redirected away from writings create', function (): void {
    $this->get('/writings/create')->assertRedirect(route('verification.notice'));
});

test('verified users can publish a writing', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $mainCategory = Category::factory()->create(['parent_id' => null]);
    $subCategory = Category::factory()->create(['parent_id' => $mainCategory->id]);

    $this->actingAs($user)->post('/writings/create', [
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
    $user = User::factory()->create();
    $mainCategory = Category::factory()->create(['parent_id' => null]);

    Writing::factory()->for($user, 'author')->count(3)->create(['created_at' => now()]);

    $this->actingAs($user)->post('/writings/create', [
        'title' => 'One too many',
        'main_category' => $mainCategory->id,
        'categories' => [$mainCategory->id],
        'text' => 'A sufficiently long body of text for validation purposes.',
    ])->assertSessionHasErrors('title');
});

test('the author can edit and delete their own writing', function (): void {
    $author = User::factory()->create();
    $writing = Writing::factory()->for($author, 'author')->create();

    $this->actingAs($author)->get('/writings/edit/'.$writing->slug)->assertOk();
    $this->actingAs($author)->delete('/writings/delete/'.$writing->slug)->assertOk();

    expect(Writing::find($writing->id))->toBeNull();
});

test('a different verified user cannot edit or delete someone else\'s writing', function (): void {
    $writing = Writing::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)->get('/writings/edit/'.$writing->slug)->assertForbidden();
    $this->actingAs($other)->delete('/writings/delete/'.$writing->slug)->assertForbidden();
});

test('an admin can edit and delete any writing', function (): void {
    $writing = Writing::factory()->create();
    $admin = actingAsAdmin();

    $this->actingAs($admin)->get('/writings/edit/'.$writing->slug)->assertOk();
    $this->actingAs($admin)->delete('/writings/delete/'.$writing->slug)->assertOk();

    expect(Writing::find($writing->id))->toBeNull();
});
