<?php

namespace Database\Factories;

use App\Models\DailySelection;
use App\Models\Writing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailySelection>
 */
class DailySelectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DailySelection>
     */
    protected $model = DailySelection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'selected_on' => $this->faker->unique()->date(),
            'writing_id' => Writing::factory(),
        ];
    }
}
