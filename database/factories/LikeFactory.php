<?php

namespace Database\Factories;

use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Like>
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'vote' => 1,
        ];
    }
}
