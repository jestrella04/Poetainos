<?php

use App\Models\Writing;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

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
        $queryCountForOneNotification = count((array) DB::getQueryLog());
        DB::flushQueryLog();

        for ($i = 0; $i < 9; $i++) {
            createDatabaseNotification($recipient, ['user_id' => $notifier->id, 'writing_id' => $writing->id]);
        }

        DB::flushQueryLog();
        actingAs($recipient)->getJson(route('notifications.index', ['tab' => 'all']))->assertOk();
        $queryCountForTenNotifications = count((array) DB::getQueryLog());
        DB::disableQueryLog();

        // Then
        expect($queryCountForTenNotifications)->toBe($queryCountForOneNotification);
    });
});

describe('the first load of the notifications page', function (): void {
    it('does not query the notifications, only when the page asks for them', function (): void {
        // Given
        $recipient = createUser();
        createDatabaseNotification($recipient, ['writing_id' => Writing::factory()->create()->id]);
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        // When
        $response = actingAs($recipient)->get(route('notifications.index'));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->missing('notifications'));
        expect(collect($queries)->filter(
            fn (string $sql): bool => str_contains($sql, 'notifications') && str_contains($sql, 'limit'),
        ))->toBeEmpty();
    });
});

describe('the notification tabs', function (): void {
    it('shows only unread notifications unless asked for all', function (string $query, int $expected): void {
        // Given
        $recipient = createUser();
        createDatabaseNotification($recipient, ['user_id' => createUser()->id]);
        createDatabaseNotification($recipient, ['user_id' => createUser()->id]);
        $recipient->notifications()->first()->markAsRead();

        // When
        $response = actingAs($recipient)->getJson(route('notifications.index').$query);

        // Then
        $response->assertOk()->assertJsonCount($expected, 'data');
    })->with([
        'by default' => ['', 1],
        'the unread tab' => ['?tab=unread', 1],
        'the all tab' => ['?tab=all', 2],
        'an unknown tab' => ['?tab=bogus', 1],
    ]);

    it('copes with notifications that carry no user or writing', function (): void {
        // Given
        $recipient = createUser();
        createDatabaseNotification($recipient, ['url' => 'https://example.com']);

        // When
        $response = actingAs($recipient)->getJson(route('notifications.index'));

        // Then
        $response->assertOk()->assertJsonPath('data.0.notifier_user', null)->assertJsonPath('data.0.notifier_writing', null);
    });
});

describe('opening a notification', function (): void {
    it('marks it as read and goes to its writing', function (): void {
        // Given
        $recipient = createUser();
        $writing = Writing::factory()->create();
        createDatabaseNotification($recipient, ['writing_id' => $writing->id]);
        $notification = $recipient->notifications()->firstOrFail();

        // When
        $response = actingAs($recipient)->get(route('notifications.show', $notification->id));

        // Then
        $response->assertRedirect($writing->path());
        expect($notification->refresh()->read_at)->not->toBeNull();
    });

    it('goes to the url the notification carries when it has one', function (): void {
        // Given
        $recipient = createUser();
        createDatabaseNotification($recipient, ['url' => 'https://example.com/somewhere']);

        // When
        $response = actingAs($recipient)->get(route('notifications.show', $recipient->notifications()->firstOrFail()->id));

        // Then
        $response->assertRedirect('https://example.com/somewhere');
    });

    it('is a 404 for a notification that does not exist or belongs to someone else', function (): void {
        // Given
        $owner = createUser();
        createDatabaseNotification($owner, ['url' => 'https://example.com']);
        $notificationId = $owner->notifications()->firstOrFail()->id;

        // When
        $forOtherUser = actingAs(createUser())->get(route('notifications.show', $notificationId));
        $missing = actingAs(createUser())->get(route('notifications.show', 'no-such-id'));

        // Then
        $forOtherUser->assertNotFound();
        $missing->assertNotFound();
    });

    it('is a 404 when the writing it points to is gone', function (): void {
        // Given
        $recipient = createUser();
        createDatabaseNotification($recipient, ['writing_id' => 999999]);

        // When
        $response = actingAs($recipient)->get(route('notifications.show', $recipient->notifications()->firstOrFail()->id));

        // Then
        $response->assertNotFound();
    });
});

describe('marking everything as read', function (): void {
    it('reads every unread notification and returns to the list', function (): void {
        // Given
        $recipient = createUser();
        createDatabaseNotification($recipient, ['url' => 'https://example.com/1']);
        createDatabaseNotification($recipient, ['url' => 'https://example.com/2']);

        // When
        $response = actingAs($recipient)->post(route('notifications.clear'));

        // Then
        $response->assertRedirect(route('notifications.index'));
        expect($recipient->unreadNotifications()->count())->toBe(0);
    });
});
