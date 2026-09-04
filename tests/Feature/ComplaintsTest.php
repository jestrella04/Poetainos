<?php

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Support\Facades\Notification;

test('reasons returns the configured complaint reasons', function (): void {
    $this->getJson('/complaints/reasons')->assertOk()->assertJson([
        'reasons' => [
            ['value' => 'spam', 'label' => 'Spam or advertising'],
            ['value' => 'abuse', 'label' => 'Harassment or abuse'],
        ],
    ]);
});

test('a complaint can be submitted for a writing, a comment, or a user', function (string $type, Closure $makeSubject): void {
    Notification::fake();

    $subject = $makeSubject();

    $this->postJson('/complaints/store', [
        'complainable_type' => $type,
        'complainable_id' => $subject->id,
        'reasons' => ['spam'],
    ])->assertOk();

    $this->assertDatabaseHas('complaints', [
        'complainable_type' => get_class($subject),
        'complainable_id' => $subject->id,
    ]);
    Notification::assertSentOnDemand(ComplaintSubmitted::class);
})->with([
    'a writing' => ['writings', fn () => Writing::factory()->create()],
    'a comment' => ['comments', fn () => Comment::factory()->create()],
    'a user' => ['users', fn () => User::factory()->create()],
]);

test('submitting a complaint requires at least one reason', function (): void {
    $writing = Writing::factory()->create();

    $this->postJson('/complaints/store', [
        'complainable_type' => 'writings',
        'complainable_id' => $writing->id,
        'reasons' => [],
    ])->assertJsonValidationErrors('reasons');
});
