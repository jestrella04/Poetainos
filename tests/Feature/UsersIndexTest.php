<?php

use App\Models\Writing;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('the authors directory', function (): void {
    it('reports the total of authors that have published writings', function (): void {
        // Given
        Writing::factory()->count(2)->for(createUser(), 'author')->create();
        Writing::factory()->for(createUser(), 'author')->create();
        createUser();

        // When
        $response = get(route('users.index'));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/PoUsersIndex', false)
                ->where('totalAuthors', 2));
    });

    it('lists the location of each author', function (): void {
        // Given
        $author = createUser();
        $author->forceFill(['extra_info' => ['location' => 'Monterrey, México']])->save();
        Writing::factory()->for($author, 'author')->create();

        // When
        $response = getJson(route('users.index'));

        // Then
        $response->assertOk()
            ->assertJsonPath('data.0.username', $author->username)
            ->assertJsonPath('data.0.location', 'Monterrey, México');
    });
});
