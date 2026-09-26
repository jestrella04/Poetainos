<?php

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\DailySelection;
use App\Models\Like;
use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\travel;

beforeEach(function (): void {
    // Components live under resources/js/components, not Inertia's default Pages directory.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

/**
 * One of every kind of activity, each a minute after the previous one.
 *
 * @return array{admin: User, author: User, reader: User, writing: Writing, comment: Comment}
 */
function seedEveryActivity(): array
{
    $admin = actingAsAdmin();
    $author = createUser();
    $reader = createUser();

    travel(1)->minutes();
    $writing = Writing::factory()->for($author, 'author')->create();
    travel(1)->minutes();
    $comment = Comment::factory()->for($writing)->for($reader, 'author')->create();
    travel(1)->minutes();
    Like::factory()->for($writing, 'likeable')->for($reader, 'user')->create();
    travel(1)->minutes();
    Like::factory()->for($comment, 'likeable')->for($author, 'user')->create();
    travel(1)->minutes();
    Shelf::factory()->create(['user_id' => $reader->id, 'writing_id' => $writing->id]);
    travel(1)->minutes();
    DailySelection::factory()->for($writing)->create();
    travel(1)->minutes();
    Complaint::factory()->for($writing, 'complainable')->create();
    travel(1)->minutes();
    Complaint::factory()->for($comment, 'complainable')->create();
    travel(1)->minutes();
    Complaint::factory()->for($reader, 'complainable')->create();

    return compact('admin', 'author', 'reader', 'writing', 'comment');
}

describe('the admin activity page', function (): void {
    it('renders with the total count of every recorded activity', function (): void {
        // Given
        ['admin' => $admin] = seedEveryActivity();

        // When
        $response = actingAs($admin)->get(route('admin.activity'));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page
            ->component('admin/PoAdminActivity')
            ->where('total', 12)
            ->has('meta.title'));
    });

    it('pages through every kind of activity, newest first', function (): void {
        // Given
        ['admin' => $admin] = seedEveryActivity();

        // When
        $firstPage = actingAs($admin)->getJson(route('admin.activity'));
        $secondPage = actingAs($admin)->getJson(route('admin.activity', ['page' => 2]));

        // Then
        expect([...$firstPage->json('data.*.kind'), ...$secondPage->json('data.*.kind')])->toBe([
            'reported_user',
            'reported_comment',
            'reported_writing',
            'writing_of_the_day',
            'bookmarked',
            'liked_comment',
            'liked_writing',
            'commented',
            'published',
            'joined',
            'joined',
            'joined',
        ]);
    });

    it('attaches who acted and the writing involved', function (): void {
        // Given
        ['admin' => $admin, 'author' => $author, 'reader' => $reader, 'writing' => $writing] = seedEveryActivity();

        // When
        $rows = collect((array) actingAs($admin)->getJson(route('admin.activity'))->json('data'))->keyBy('kind');

        // Then
        expect($rows['liked_comment']['user']['id'])->toBe($author->id)
            ->and($rows['liked_comment']['writing']['slug'])->toBe($writing->slug)
            ->and($rows['commented']['user']['username'])->toBe($reader->username)
            ->and($rows['reported_comment']['writing']['id'])->toBe($writing->id)
            ->and($rows['reported_comment']['user'])->toBeNull()
            ->and($rows['reported_user']['user']['id'])->toBe($reader->id)
            ->and($rows['reported_user']['writing'])->toBeNull()
            ->and($rows['writing_of_the_day']['user'])->toBeNull();
    });

    it('is forbidden to non-admins', function (): void {
        // Given
        $user = createUser();

        // When
        $response = actingAs($user)->getJson(route('admin.activity'));

        // Then
        $response->assertForbidden();
    });
});
