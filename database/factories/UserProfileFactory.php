<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Database\Factories\Concerns\StoresDemoImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    use StoresDemoImages;

    private const int BIO_MAX_LENGTH = 300;

    /**
     * Define the model's default state: half of the profiles get a bio and,
     * independently, half get an avatar.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fn (): ?string => $this->faker->boolean()
                ? $this->faker->text($this->faker->numberBetween(50, self::BIO_MAX_LENGTH))
                : null,
            'avatar' => fn (): ?string => $this->faker->boolean()
                ? $this->storeDemoImage('images/logo-maskable.png', 'avatars')
                : null,
        ];
    }
}
