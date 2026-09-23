<?php

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Concerns\StoresDemoImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    use StoresDemoImages;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    private const int BIO_MAX_LENGTH = 300;

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
            'extra_info' => fn (): ?array => $this->randomExtraInfo(),
        ];
    }

    /**
     * Half of the users get a bio and, independently, half get an avatar.
     *
     * @return array{bio?: string, avatar?: string}|null
     */
    private function randomExtraInfo(): ?array
    {
        $extraInfo = [];

        if ($this->faker->boolean()) {
            $extraInfo['bio'] = $this->randomBio();
        }

        if ($this->faker->boolean()) {
            $extraInfo['avatar'] = $this->storeDemoImage('images/logo-maskable.png', 'avatars');
        }

        return $extraInfo === [] ? null : $extraInfo;
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

    private function randomBio(): string
    {
        return $this->faker->text($this->faker->numberBetween(50, self::BIO_MAX_LENGTH));
    }
}
