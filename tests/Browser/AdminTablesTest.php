<?php

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Page;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Tests\Browser\Pages\AdminTablePage;

use function Pest\Laravel\actingAs;

describe('the admin tables', function (): void {
    it('delete a row after confirming', function (string $routeName, Closure $createRow, string $messageKey): void {
        // Given
        $row = $createRow();
        actingAs(actingAsAdmin());

        // When
        $table = AdminTablePage::open($routeName)->deleteRow($row->id);

        // Then
        $table->browser()->assertSee(AdminTablePage::message($messageKey))->assertNoJavaScriptErrors();
        expect($row->fresh())->toBeNull();
    })->with([
        'writings' => ['admin.writings', fn (): Writing => Writing::factory()->create(), 'writings.writing-deleted'],
        'tags' => ['admin.tags', fn (): Tag => Tag::factory()->create(), 'tags.tag-deleted'],
        'categories' => ['admin.categories', fn (): Category => Category::factory()->create(), 'categories.category-deleted'],
        'pages' => ['admin.pages', fn (): Page => createPage(), 'pages.page-deleted'],
    ]);

    it('delete a user once the admin confirms their password', function (): void {
        // Given
        $user = createUser();
        actingAs(actingAsAdmin());

        // When
        $table = AdminTablePage::open('admin.users')->deleteRow($user->id, 'password');

        // Then
        $table->browser()->assertSee(AdminTablePage::message('users.user-deleted'))->assertNoJavaScriptErrors();
        expect(User::find($user->id))->toBeNull();
    });

    it('create a category', function (): void {
        // Given
        $name = fakeTitle();
        actingAs(actingAsAdmin());

        // When
        $table = AdminTablePage::open('admin.categories')->createCategory($name, fake()->sentence());

        // Then
        $table->browser()->assertSee(AdminTablePage::message('categories.category-created'))->assertNoJavaScriptErrors();
        expect(Category::where('name', $name)->exists())->toBeTrue();
    });

    it('rename a page', function (): void {
        // Given
        $page = createPage();
        $title = fakeTitle();
        actingAs(actingAsAdmin());

        // When
        $table = AdminTablePage::open('admin.pages')->renamePage($page->id, $title);

        // Then
        $table->browser()->assertSee(AdminTablePage::message('pages.page-updated'))->assertNoJavaScriptErrors();
        expect($page->refresh()->title)->toBe($title);
    });

    it('close a complaint with a note', function (): void {
        // Given
        $complaint = Complaint::factory()->for(Writing::factory(), 'complainable')->create();
        $note = fake()->sentence();
        actingAs(actingAsAdmin());

        // When
        $table = AdminTablePage::open('admin.complaints')->closeComplaint($complaint->id, $note);

        // Then
        $table->browser()->assertSee(AdminTablePage::message('complaints.complaint-closed'))->assertNoJavaScriptErrors();
        expect($complaint->refresh()->closed_comment)->toBe($note);
    });
});
