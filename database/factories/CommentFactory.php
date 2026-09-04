<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'writing_id' => Writing::factory(),
            'message' => $this->faker->paragraph,
        ];
    }
}
