<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Writing;
use Database\Factories\Concerns\StoresDemoImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Writing>
 */
class WritingFactory extends Factory
{
    use StoresDemoImages;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Writing>
     */
    protected $model = Writing::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->text(45),
            'slug' => $this->faker->unique()->slug(3),
            'text' => $this->faker->paragraphs($this->faker->numberBetween(2, 10), true),
            'extra_info' => fn (): ?array => $this->faker->boolean() ? ['cover' => $this->storeDemoImage('images/cover.jpg', 'covers')] : null,
        ];
    }
}
