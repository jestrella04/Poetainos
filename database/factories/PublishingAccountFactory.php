<?php

namespace Database\Factories;

use App\Models\PublishingAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublishingAccount>
 */
class PublishingAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'provider' => PublishingAccount::THREADS,
            'account_id' => (string) fake()->randomNumber(9),
            'access_token' => fake()->sha256(),
            'expires_at' => Carbon::now()->addDays(60),
        ];
    }
}
