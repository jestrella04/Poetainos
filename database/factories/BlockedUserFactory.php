<?php

namespace Database\Factories;

use App\Models\BlockedUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedUser>
 */
class BlockedUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<BlockedUser>
     */
    protected $model = BlockedUser::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'blocked_user_id' => User::factory(),
        ];
    }
}
