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
        $response->assertOk()->assertJson(['reasons' => getSiteConfig('complaints')]);
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
            'complainable_id' => fake()->numberBetween(100000, 999999),
            'reasons' => ['spam'],
        ]);

        // Then
        $response->assertNotFound();
    });
});

describe('validating a complaint', function (): void {
    it('rejects a reason that is not configured', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $response = postJson('/complaints/store', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => [fake()->lexify('reason-????')],
        ]);

        // Then
        $response->assertJsonValidationErrors('reasons.0');
    });

    it('accepts reasons configured as plain strings', function (): void {
        // Given
        Notification::fake();
        $reasons = [fake()->unique()->word(), fake()->unique()->word()];
        config(['poetainos.complaints' => $reasons]);
        $writing = Writing::factory()->create();

        // When
        $response = postJson('/complaints/store', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => [$reasons[0]],
        ]);

        // Then
        $response->assertOk();
    });

    it('limits how many reasons can be sent', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $response = postJson('/complaints/store', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => array_fill(0, 11, 'spam'),
        ]);

        // Then
        $response->assertJsonValidationErrors('reasons');
    });

    it('stores the comment the reporter wrote', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $comment = fake()->sentence();

        // When
        postJson('/complaints/store', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => ['spam'],
            'comment' => $comment,
        ])->assertOk();

        // Then
        assertDatabaseHas('complaints', ['comment' => $comment]);
    });
});
