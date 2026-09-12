<?php

use App\Models\Writing;
use App\Notifications\WritingShelved;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

test('a user can shelve and unshelve a writing', function (): void {
    Notification::fake();

    $author = createUser();
    $writing = Writing::factory()->for($author, 'author')->create();
    $reader = createUser();

    actingAs($reader)->post("/shelves/{$writing->slug}/store")
        ->assertJson(['method' => 'store', 'count' => 1]);

    Notification::assertSentTo($author, WritingShelved::class);

    actingAs($reader)->post("/shelves/{$writing->slug}/store")
        ->assertJson(['method' => 'destroy', 'count' => 0]);
});

test('shelving your own writing does not notify you', function (): void {
    Notification::fake();

    $author = createUser();
    $writing = Writing::factory()->for($author, 'author')->create();

    actingAs($author)->post("/shelves/{$writing->slug}/store");

    Notification::assertNothingSent();
});

test('deleting a shelf entry only detaches the acting user', function (): void {
    $writing = Writing::factory()->create();
    $reader = createUser();
    $otherReader = createUser();

    actingAs($reader)->post("/shelves/{$writing->slug}/store");
    actingAs($otherReader)->post("/shelves/{$writing->slug}/store");

    actingAs($reader)->delete("/shelves/{$writing->slug}/delete")
        ->assertJson(['method' => 'destroy', 'count' => 1]);
});
