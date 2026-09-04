<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the role grants admin access.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'extra_info' => [
                'permissions' => [
                    ['name' => 'admin', 'enabled' => true],
                ],
            ],
        ]);
    }
}
