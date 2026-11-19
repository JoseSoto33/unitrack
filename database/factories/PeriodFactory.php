<?php

namespace Database\Factories;

use App\Enums\PeriodType;
use App\Models\Career;
use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Period>
 */
class PeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'career_id' => Career::factory(),
            'type' => fake()->randomElement(PeriodType::cases()),
            'number' => fake()->numberBetween(1, 3),
            'starts_on' => fake()->dateTimeBetween('-6 months', 'now'),
            'ends_on' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'is_current' => false,
        ];
    }

    /**
     * Indicate that the period is the current one.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
        ]);
    }
}
