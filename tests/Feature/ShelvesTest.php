<?php

use App\Models\Shelf;
use App\Models\Writing;
use App\Notifications\WritingShelved;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

describe('shelving a writing', function (): void {
    it('allows a user to shelve and unshelve a writing', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $reader = createUser();

        // When
        $shelveResponse = actingAs($reader)->post("/shelves/{$writing->slug}/store");

        // Then
        $shelveResponse->assertJson(['method' => 'store', 'count' => 1]);
        Notification::assertSentTo($author, WritingShelved::class);

        // When
        $unshelveResponse = actingAs($reader)->post("/shelves/{$writing->slug}/store");

        // Then
        $unshelveResponse->assertJson(['method' => 'destroy', 'count' => 0]);
    });

    it('does not notify the author when they shelve their own writing', function (): void {
        // Given
        Notification::fake();
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();

        // When
        actingAs($author)->post("/shelves/{$writing->slug}/store");

        // Then
        Notification::assertNothingSent();
    });

    it('only detaches the acting user when they take the writing off the shelf', function (): void {
        // Given
        $writing = Writing::factory()->create();
        $reader = createUser();
        $otherReader = createUser();
        actingAs($reader)->post("/shelves/{$writing->slug}/store");
        actingAs($otherReader)->post("/shelves/{$writing->slug}/store");

        // When
        $response = actingAs($reader)->post("/shelves/{$writing->slug}/store");

        // Then
        $response->assertJson(['method' => 'destroy', 'count' => 1]);
    });
});

describe('a double click on the shelf button', function (): void {
    it('reports the shelving instead of failing when it was already created', function (): void {
        // Given
        $writing = Writing::factory()->create();
        Shelf::creating(fn () => throw new UniqueConstraintViolationException('sqlite', 'insert', [], new Exception('duplicate')));

        // When
        $response = actingAs(createUser())->post("/shelves/{$writing->slug}/store");

        // Then
        $response->assertOk()->assertJson(['method' => 'store']);
    });
});
