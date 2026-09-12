<?php

use App\Models\Comment;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('reasons returns the configured complaint reasons', function (): void {
    getJson('/complaints/reasons')->assertOk()->assertJson([
        'reasons' => [
            ['value' => 'spam', 'label' => 'Spam or advertising'],
            ['value' => 'abuse', 'label' => 'Harassment or abuse'],
        ],
    ]);
});

it('can submit a complaint for a writing, a comment, or a user', function (string $type, Closure $makeSubject): void {
    Notification::fake();

    $subject = $makeSubject();

    postJson('/complaints/store', [
        'complainable_type' => $type,
        'complainable_id' => $subject->id,
        'reasons' => ['spam'],
    ])->assertOk();

    assertDatabaseHas('complaints', [
        'complainable_type' => get_class($subject),
        'complainable_id' => $subject->id,
    ]);
    Notification::assertSentOnDemand(ComplaintSubmitted::class);
})->with([
    'a writing' => ['writings', fn () => Writing::factory()->create()],
    'a comment' => ['comments', fn () => Comment::factory()->create()],
    'a user' => ['users', fn () => createUser()],
]);

test('submitting a complaint requires at least one reason', function (): void {
    $writing = Writing::factory()->create();

    postJson('/complaints/store', [
        'complainable_type' => 'writings',
        'complainable_id' => $writing->id,
        'reasons' => [],
    ])->assertJsonValidationErrors('reasons');
});

test('submitting a complaint about a nonexistent subject 404s instead of crashing', function (): void {
    postJson('/complaints/store', [
        'complainable_type' => 'writings',
        'complainable_id' => 999999,
        'reasons' => ['spam'],
    ])->assertNotFound();
});
