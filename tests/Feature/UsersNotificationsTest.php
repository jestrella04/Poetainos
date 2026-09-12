<?php

use App\Models\User;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

/**
 * @param  array<string, mixed>  $data
 */
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

describe('the notifications index', function (): void {
    it('attaches the notifier user and writing without querying per notification', function (): void {
        // Given
        $recipient = createUser();
        $notifier1 = createUser();
        $notifier2 = createUser();
        $writing1 = Writing::factory()->create();
        $writing2 = Writing::factory()->create();

        createDatabaseNotification($recipient, ['user_id' => $notifier1->id, 'writing_id' => $writing1->id], now()->subMinute());
        createDatabaseNotification($recipient, ['user_id' => $notifier2->id, 'writing_id' => $writing2->id], now());

        // When
        $response = actingAs($recipient)->getJson(route('notifications.index', ['tab' => 'all']));

        // Then
        $response->assertOk();
        $response->assertJsonPath('data.0.notifier_user.id', $notifier2->id);
        $response->assertJsonPath('data.0.notifier_writing.id', $writing2->id);
        $response->assertJsonPath('data.1.notifier_user.id', $notifier1->id);
        $response->assertJsonPath('data.1.notifier_writing.id', $writing1->id);
    });

    it('keeps the query count from scaling with the number of notifications', function (): void {
        // Given
        $recipient = createUser();
        $notifier = createUser();
        $writing = Writing::factory()->create();

        createDatabaseNotification($recipient, ['user_id' => $notifier->id, 'writing_id' => $writing->id]);

        // When
        DB::enableQueryLog();
        actingAs($recipient)->getJson(route('notifications.index', ['tab' => 'all']))->assertOk();
        $queryCountForOneNotification = count(DB::getQueryLog());
        DB::flushQueryLog();

        for ($i = 0; $i < 9; $i++) {
            createDatabaseNotification($recipient, ['user_id' => $notifier->id, 'writing_id' => $writing->id]);
        }

        DB::flushQueryLog();
        actingAs($recipient)->getJson(route('notifications.index', ['tab' => 'all']))->assertOk();
        $queryCountForTenNotifications = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Then
        expect($queryCountForTenNotifications)->toBe($queryCountForOneNotification);
    });
});
