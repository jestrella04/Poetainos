<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName,
            'name' => $this->randomName(),
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$Qh9yxR9v6OfLQU5Lw61hQOLVvdegUt7WxG9/HXGVvxZB2Wd.Si.aK', // password
            'email_verified_at' => now(),
        ];
    }

    /**
     * Give the user a profile, as the seeded demo users have.
     */
    public function withProfile(): static
    {
        return $this->has(UserProfile::factory(), 'profile');
    }

    /**
     * Picks at random between no name, first name only, or first plus last name.
     */
    private function randomName(): ?string
    {
        return match ($this->faker->numberBetween(0, 2)) {
            0 => null,
            1 => $this->faker->firstName(),
            default => $this->faker->firstName().' '.$this->faker->lastName(),
        };
    }
}
