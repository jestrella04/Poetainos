<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Writing;
use Tests\Browser\Pages\ExplorePage;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\UserProfilePage;
use Tests\Browser\Pages\WritingEntry;
use Tests\Browser\Pages\WritingPage;

describe('browsing main sections', function () {
    it('shows the home page with published writings', function () {
        $writings = Writing::factory()->count(3)->create();

        $browser = HomePage::open()->browser();

        foreach ($writings as $writing) {
            $browser->assertSeeIn((new WritingEntry($writing))->title(), $writing->title);
        }

        $browser->assertNoJavaScriptErrors();
    });

    it('shows the explore page', function () {
        $category = Category::factory()->create(['parent_id' => null]);
        Writing::factory()->create()->categories()->attach($category);

        ExplorePage::open()
            ->browser()
            ->assertVisible(ExplorePage::TITLE)
            ->assertSeeIn(ExplorePage::categoryName($category), $category->name)
            ->assertNoJavaScriptErrors();
    });

    it('shows a single writing page with its title and author', function () {
        $author = User::factory()->create(['name' => 'Ada Lovelace']);
        $writing = Writing::factory()->for($author, 'author')->create();
        $entry = new WritingEntry($writing);

        WritingPage::open($writing)
            ->browser()
            ->assertSeeIn($entry->title(), $writing->title)
            ->assertSeeIn($entry->author(), 'Ada Lovelace')
            ->assertNoJavaScriptErrors();
    });

    it('shows a user profile page', function () {
        $user = User::factory()->create(['name' => 'Grace Hopper']);

        UserProfilePage::open($user)
            ->browser()
            ->assertSeeIn(UserProfilePage::NAME, 'Grace Hopper')
            ->assertNoJavaScriptErrors();
    });
});
