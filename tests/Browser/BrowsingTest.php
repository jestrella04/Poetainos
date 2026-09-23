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
        $writings = Writing::factory()->count(fake()->numberBetween(1, 5))->create();

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
        $name = fake()->firstName().' '.fake()->lastName();
        $author = User::factory()->create(['name' => $name]);
        $writing = Writing::factory()->for($author, 'author')->create();
        $entry = new WritingEntry($writing);

        WritingPage::open($writing)
            ->browser()
            ->assertSeeIn($entry->title(), $writing->title)
            ->assertSeeIn($entry->author(), $name)
            ->assertNoJavaScriptErrors();
    });

    it('shows a user profile page', function () {
        $name = fake()->firstName().' '.fake()->lastName();
        $user = User::factory()->create(['name' => $name]);

        UserProfilePage::open($user)
            ->browser()
            ->assertSeeIn(UserProfilePage::NAME, $name)
            ->assertNoJavaScriptErrors();
    });
});
