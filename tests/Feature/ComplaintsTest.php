<?php

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\travel;

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
        $response = postJson('/complaints', [
            'complainable_type' => $type,
            'complainable_id' => $subject->id,
            'reasons' => ['spam'],
        ]);

        // Then
        $response->assertNoContent();
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

    it('emails the admins once per reported item and hour', function (): void {
        // Given
        Notification::fake();
        $writing = Writing::factory()->create();
        $complaint = ['complainable_type' => 'writings', 'complainable_id' => $writing->id, 'reasons' => ['spam']];

        // When
        postJson('/complaints', $complaint)->assertNoContent();
        postJson('/complaints', $complaint)->assertNoContent();
        travel(61)->minutes();
        postJson('/complaints', $complaint)->assertNoContent();

        // Then
        expect(Complaint::count())->toBe(3);
        Notification::assertSentOnDemandTimes(ComplaintSubmitted::class, 2);
    });

    it('caps how many complaints one visitor can file in a day', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $complaint = ['complainable_type' => 'writings', 'complainable_id' => $writing->id, 'reasons' => ['spam']];
        foreach (range(1, 3) as $window) {
            foreach (range(1, 10) as $attempt) {
                postJson('/complaints', $complaint);
            }
            travel(1)->minutes();
        }

        // When
        $response = postJson('/complaints', $complaint);

        // Then
        $response->assertTooManyRequests();
    });

    it('requires at least one reason', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $response = postJson('/complaints', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => [],
        ]);

        // Then
        $response->assertJsonValidationErrors('reasons');
    });

    it('404s instead of crashing for a nonexistent subject', function (): void {
        // When
        $response = postJson('/complaints', [
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
        $response = postJson('/complaints', [
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
        $response = postJson('/complaints', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => [$reasons[0]],
        ]);

        // Then
        $response->assertNoContent();
    });

    it('limits how many reasons can be sent', function (): void {
        // Given
        $writing = Writing::factory()->create();

        // When
        $response = postJson('/complaints', [
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
        postJson('/complaints', [
            'complainable_type' => 'writings',
            'complainable_id' => $writing->id,
            'reasons' => ['spam'],
            'comment' => $comment,
        ])->assertNoContent();

        // Then
        assertDatabaseHas('complaints', ['comment' => $comment]);
    });
});

describe('closing a complaint', function (): void {
    it('lets an admin close a complaint with a note and return to the table', function (): void {
        // Given
        $complaint = Complaint::factory()->for(Writing::factory(), 'complainable')->create();
        $note = fake()->sentence();
        $admin = actingAsAdmin();

        // When
        $response = actingAs($admin)->from(route('admin.complaints'))->put(route('admin.complaints.close', $complaint), [
            'closed_comment' => $note,
        ]);

        // Then
        $response->assertRedirect(route('admin.complaints'))->assertInertiaFlash('message', 'complaints.complaint-closed');
        $complaint->refresh();
        expect($complaint->closed_at)->not->toBeNull();
        expect($complaint->closed_comment)->toBe($note);
    });

    it('is forbidden for non-admins', function (): void {
        // Given
        $complaint = Complaint::factory()->for(Writing::factory(), 'complainable')->create();

        // When
        $response = actingAs(createUser())->putJson(route('admin.complaints.close', $complaint));

        // Then
        $response->assertForbidden();
        expect($complaint->refresh()->closed_at)->toBeNull();
    });
});
