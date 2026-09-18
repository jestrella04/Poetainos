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
     * @var class-string<Comment>
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'writing_id' => Writing::factory(),
            'message' => implode("\n\n", $this->faker->paragraphs($this->faker->numberBetween(1, 4))),
        ];
    }
}
