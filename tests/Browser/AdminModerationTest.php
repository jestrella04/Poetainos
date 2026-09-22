<?php

use App\Models\Category;
use App\Models\Writing;
use Tests\Browser\Pages\AdminWritingsPage;
use Tests\Browser\Pages\WritingFormPage;
use Tests\Browser\Pages\WritingPage;

use function Pest\Laravel\actingAs;

describe('admin moderation of another user\'s writing', function () {
    // Skipped: the writing form always submits multipart/form-data (it has an
    // optional cover-image file field), and pest-plugin-browser's embedded
    // LaravelHttpServer driver doesn't parse multipart bodies yet (see
    // vendor/pestphp/pest-plugin-browser/src/Drivers/LaravelHttpServer.php,
    // `[], // @TODO files...`). The `_method: PUT` spoof field never reaches
    // Laravel, so every submit hits the PUT-only update route as a plain POST
    // and gets a 405. Re-enable once that upstream gap is fixed.
    it('lets an admin edit another user\'s writing', function () {
        $admin = actingAsAdmin([
            'extra_info' => ['agreement' => ['terms_of_use' => 'on', 'privacy_policy' => 'on']],
        ]);
        $otherAuthor = createUser();
        $writing = Writing::factory()->for($otherAuthor, 'author')->create();
        $mainCategory = Category::factory()->create(['parent_id' => null]);
        $altCategory = Category::factory()->create(['parent_id' => $mainCategory->id]);
        $writing->categories()->attach([$mainCategory->id, $altCategory->id]);

        actingAs($admin);

        $title = fakeTitle();

        $form = WritingPage::open($writing)->goToEditForm();

        $form->browser()->assertPathIs('/writings/edit/'.$writing->slug);

        $form->fillTitle($title)
            ->submit()
            ->browser()
            ->assertVisible(WritingFormPage::SUCCESS_ALERT)
            ->assertNoJavaScriptErrors();

        expect($writing->refresh()->title)->toBe($title);
    })->skip('pest-plugin-browser does not parse multipart form bodies yet, so the writing form\'s PUT submit is received as a 405');

    it('lets an admin delete another user\'s writing', function () {
        $admin = actingAsAdmin([
            'extra_info' => ['agreement' => ['terms_of_use' => 'on', 'privacy_policy' => 'on']],
        ]);
        $otherAuthor = createUser();
        $writing = Writing::factory()->for($otherAuthor, 'author')->create();

        actingAs($admin);

        WritingFormPage::openEdit($writing)
            ->deleteWriting()
            ->browser()
            ->assertPathIs('/')
            ->assertNoJavaScriptErrors();

        expect(Writing::find($writing->id))->toBeNull();
    });

    // NOTE: the admin writings table (PoAdminWritings.vue) has a delete button
    // that is not wired to any handler (href="#", no confirmation, no request).
    // Admin deletion currently only works through the writing's own edit page
    // (tested above), which `canEdit()` grants admins the same as owners.
    it('lists writings on the admin moderation page', function () {
        $author = createUser();
        $writing = Writing::factory()->for($author, 'author')->create();
        $admin = actingAsAdmin();

        actingAs($admin);

        AdminWritingsPage::open()
            ->browser()
            ->assertSeeIn(AdminWritingsPage::writingRow($writing), $writing->title)
            ->assertNoJavaScriptErrors();
    });
});
