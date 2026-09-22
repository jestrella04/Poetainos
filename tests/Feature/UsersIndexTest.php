<?php

use App\Models\Writing;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('the authors directory', function (): void {
    it('reports the total of authors that have published writings', function (): void {
        // Given
        $authorsCount = fake()->numberBetween(1, 5);
        foreach (range(1, $authorsCount) as $author) {
            Writing::factory()->count(fake()->numberBetween(1, 3))->for(createUser(), 'author')->create();
        }
        createUser();

        // When
        $response = get(route('users.index'));

        // Then
        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/PoUsersIndex', false)
                ->where('totalAuthors', $authorsCount));
    });

    it('lists the location of each author', function (): void {
        // Given
        $author = createUser();
        $location = fake()->city().', '.fake()->country();
        $author->forceFill(['extra_info' => ['location' => $location]])->save();
        Writing::factory()->for($author, 'author')->create();

        // When
        $response = getJson(route('users.index'));

        // Then
        $response->assertOk()
            ->assertJsonPath('data.0.username', $author->username)
            ->assertJsonPath('data.0.location', $location);
    });

    it('does not query the authors on the first page load', function (): void {
        // Given
        Writing::factory()->for(createUser(), 'author')->create();
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        // When
        $response = get(route('users.index'));

        // Then
        $response->assertOk()->assertInertia(fn ($page) => $page->missing('users'));
        expect(collect($queries)->filter(fn (string $sql): bool => str_contains($sql, 'bio')))->toBeEmpty();
    });
});
