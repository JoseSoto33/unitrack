<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'user_id' => fn (array $attributes): int => Subject::query()
                ->findOrFail((int) $attributes['subject_id'])
                ->period
                ->user_id,
        ];
    }
}
