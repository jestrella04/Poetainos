<?php

use App\Models\Category;
use App\Models\Writing;
use Tests\Browser\Pages\WritingFormPage;

use function Pest\Laravel\actingAs;

describe('publishing a writing', function () {
    // Skipped: two upstream pest-plugin-browser gaps block this end-to-end flow.
    // 1) click() on an <input> element (the category selects) hangs indefinitely
    //    in this Playwright 1.63.0 + pest-plugin-browser 4.3.1 + headless-shell
    //    combo — confirmed by bisection: click() on <button>/<a> targets returns
    //    normally, click() on any <input> (text or role="combobox") never gets a
    //    response back over the driver's websocket. type()/fill() on the same
    //    inputs is unaffected.
    // 2) Even past that, the form always submits multipart/form-data (optional
    //    cover-image field), and pest-plugin-browser's embedded LaravelHttpServer
    //    driver doesn't parse multipart bodies yet (see
    //    vendor/pestphp/pest-plugin-browser/src/Drivers/LaravelHttpServer.php,
    //    `[], // @TODO files...`), so `_method: PUT` never reaches Laravel and
    //    the edit submit 405s.
    // Re-enable once both upstream gaps are fixed.
    it('creates, edits, and deletes a writing end-to-end', function () {
        $author = createUser();
        $parentCategory = Category::factory()->create(['parent_id' => null, 'name' => 'Poetry']);
        $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id, 'name' => 'Sonnets']);

        actingAs($author);

        WritingFormPage::openCreate()
            ->fillTitle('My Browser-Tested Poem')
            ->selectMainCategory($parentCategory)
            ->selectAltCategory($childCategory)
            ->fillText('This is a writing created end-to-end by a real browser test.')
            ->acceptAgreements()
            ->submit()
            ->browser()
            ->assertVisible(WritingFormPage::SUCCESS_ALERT)
            ->assertNoJavaScriptErrors();

        $writing = Writing::where('title', 'My Browser-Tested Poem')->firstOrFail();

        WritingFormPage::openEdit($writing)
            ->fillTitle('My Edited Browser-Tested Poem')
            ->submit()
            ->browser()
            ->assertVisible(WritingFormPage::SUCCESS_ALERT)
            ->assertNoJavaScriptErrors();

        expect($writing->refresh()->title)->toBe('My Edited Browser-Tested Poem');

        WritingFormPage::openEdit($writing)
            ->deleteWriting()
            ->browser()
            ->assertPathIs('/')
            ->assertNoJavaScriptErrors();

        expect(Writing::find($writing->id))->toBeNull();
    })->skip('pest-plugin-browser hangs on click() against <input> elements and does not parse multipart form bodies yet');
});
