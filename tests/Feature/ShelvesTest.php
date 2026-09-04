<?php

use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingShelved;
use Illuminate\Support\Facades\Notification;

test('a user can shelve and unshelve a writing', function (): void {
    Notification::fake();

    $author = User::factory()->create();
    $writing = Writing::factory()->for($author, 'author')->create();
    $reader = User::factory()->create();

    $this->actingAs($reader)->post("/shelves/{$writing->slug}/store")
        ->assertJson(['method' => 'store', 'count' => 1]);

    Notification::assertSentTo($author, WritingShelved::class);

    $this->actingAs($reader)->post("/shelves/{$writing->slug}/store")
        ->assertJson(['method' => 'destroy', 'count' => 0]);
});

test('shelving your own writing does not notify you', function (): void {
    Notification::fake();

    $author = User::factory()->create();
    $writing = Writing::factory()->for($author, 'author')->create();

    $this->actingAs($author)->post("/shelves/{$writing->slug}/store");

    Notification::assertNothingSent();
});

test('deleting a shelf entry only detaches the acting user', function (): void {
    $writing = Writing::factory()->create();
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();

    $this->actingAs($reader)->post("/shelves/{$writing->slug}/store");
    $this->actingAs($otherReader)->post("/shelves/{$writing->slug}/store");

    $this->actingAs($reader)->delete("/shelves/{$writing->slug}/delete")
        ->assertJson(['method' => 'destroy', 'count' => 1]);
});
