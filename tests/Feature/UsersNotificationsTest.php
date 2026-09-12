<?php

use App\Models\User;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function createDatabaseNotification(User $recipient, array $data, ?Carbon $createdAt = null): void
{
    $createdAt ??= now();

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\WritingLiked',
        'notifiable_type' => User::class,
        'notifiable_id' => $recipient->id,
        'data' => json_encode($data),
        'read_at' => null,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
}

test('index attaches the notifier user and writing without querying per notification', function (): void {
    $recipient = User::factory()->create();
    $notifiers = User::factory()->count(2)->create();
    $writings = Writing::factory()->count(2)->create();

    createDatabaseNotification($recipient, ['user_id' => $notifiers[0]->id, 'writing_id' => $writings[0]->id], now()->subMinute());
    createDatabaseNotification($recipient, ['user_id' => $notifiers[1]->id, 'writing_id' => $writings[1]->id], now());

    $response = $this->actingAs($recipient)->getJson(route('notifications.index', ['tab' => 'all']));

    $response->assertOk();
    $response->assertJsonPath('data.0.notifier_user.id', $notifiers[1]->id);
    $response->assertJsonPath('data.0.notifier_writing.id', $writings[1]->id);
    $response->assertJsonPath('data.1.notifier_user.id', $notifiers[0]->id);
    $response->assertJsonPath('data.1.notifier_writing.id', $writings[0]->id);
});

test('index query count does not scale with the number of notifications', function (): void {
    $recipient = User::factory()->create();
    $notifier = User::factory()->create();
    $writing = Writing::factory()->create();

    createDatabaseNotification($recipient, ['user_id' => $notifier->id, 'writing_id' => $writing->id]);

    DB::enableQueryLog();
    $this->actingAs($recipient)->getJson(route('notifications.index', ['tab' => 'all']))->assertOk();
    $queryCountForOneNotification = count(DB::getQueryLog());
    DB::flushQueryLog();

    for ($i = 0; $i < 9; $i++) {
        createDatabaseNotification($recipient, ['user_id' => $notifier->id, 'writing_id' => $writing->id]);
    }

    DB::flushQueryLog();
    $this->actingAs($recipient)->getJson(route('notifications.index', ['tab' => 'all']))->assertOk();
    $queryCountForTenNotifications = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCountForTenNotifications)->toBe($queryCountForOneNotification);
});
