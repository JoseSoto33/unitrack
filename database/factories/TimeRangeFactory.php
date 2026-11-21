<?php

namespace Database\Factories;

use App\Models\ClassDay;
use App\Models\TimeRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeRange>
 */
class TimeRangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_day_id' => ClassDay::factory(),
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ];
    }
}
