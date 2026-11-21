<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use App\Models\ClassDay;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassDay>
 */
class ClassDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'day_of_week' => fake()->randomElement(DayOfWeek::cases()),
            'modality' => null,
        ];
    }
}
