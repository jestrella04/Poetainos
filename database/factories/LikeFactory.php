<?php

namespace Database\Factories;

use App\Models\Writing;
use App\Models\User;
use App\Models\Like;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'writing_id' => Writing::factory(),
            'user_id' => User::factory(),
            'like' => $this->faker->boolean(68)
        ];
    }
}
