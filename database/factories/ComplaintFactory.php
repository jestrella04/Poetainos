<?php

namespace Database\Factories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Complaint>
     */
    protected $model = Complaint::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'reasons' => [$this->faker->randomElement(['spam', 'abuse'])],
            'comment' => $this->faker->optional()->sentence(),
        ];
    }
}
