<?php

namespace Database\Factories;

use App\Models\Permission;
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
     * @var class-string<Role>
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
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
        return $this->afterCreating(function (Role $role): void {
            $role->permissions()->attach(Permission::firstOrCreate(['name' => Permission::ADMIN]));
        });
    }
}
