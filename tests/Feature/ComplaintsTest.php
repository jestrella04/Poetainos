<?php

use App\Models\Comment;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('the reasons endpoint', function (): void {
    it('returns the configured complaint reasons', function (): void {
        // When
        $response = getJson('/complaints/reasons');

        // Then
        $response->assertOk()->assertJson([
            'reasons' => [
                ['value' => 'spam', 'label' => 'Spam or advertising'],
                ['value' => 'abuse', 'label' => 'Harassment or abuse'],
            ],
        ]);
    });
});

describe('submitting a complaint', function (): void {
    it('can be submitted for a writing, a comment, or a user', function (string $type, Closure $makeSubject): void {
        // Given
        Notification::fake();
        $subject = $makeSubject();

        // When
        $response = postJson('/complaints/store', [
            'complainable_type' => $type,
            'complainable_id' => $subject->id,
            'reasons' => ['spam'],
        ]);

        // Then
        $response->assertOk();
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

    it('requires at least one reason', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $response = postJson('/complaints/store', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => [],
        ]);

        // Then
        $response->assertJsonValidationErrors('reasons');
    });

    it('404s instead of crashing for a nonexistent subject', function (): void {
        // When
        $response = postJson('/complaints/store', [
            'complainable_type' => 'writings',
            'complainable_id' => 999999,
            'reasons' => ['spam'],
        ]);

        // Then
        $response->assertNotFound();
    });
});
