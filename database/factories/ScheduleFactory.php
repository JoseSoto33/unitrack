<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
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
            'day_of_week' => fake()->randomElement(DayOfWeek::cases()),
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ];
    }

    /**
     * Set a specific day and time block.
     */
    public function block(int $day, string $startsAt, string $endsAt): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $day,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }
}
