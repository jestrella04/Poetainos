<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Writing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Writing>
 */
class WritingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Writing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->text(45),
            'slug' => $this->faker->unique()->slug(3),
            'text' => $this->faker->paragraph,
        ];
    }
}
