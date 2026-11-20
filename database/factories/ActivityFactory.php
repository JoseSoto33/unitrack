<?php

namespace Database\Factories;

use App\Enums\ActivityModality;
use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
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
            'title' => fake()->randomElement([
                'Examen parcial',
                'Quiz 1',
                'Taller grupal',
                'Informe de laboratorio',
                'Exposición final',
                'Ensayo argumentativo',
            ]),
            'type' => fake()->randomElement(ActivityType::cases()),
            'modality' => fake()->randomElement(ActivityModality::cases()),
            'assigned_on' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'due_at' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'delivered_at' => null,
            'percentage' => fake()->randomElement([10, 15, 20, 25]),
            'grade' => null,
        ];
    }

    /**
     * Indicate that the activity was delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivered_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the activity is overdue and undelivered.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => now()->subWeek(),
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the activity has a grade.
     */
    public function graded(float $grade): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => $grade,
        ]);
    }
}
