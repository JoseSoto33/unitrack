<?php

namespace Database\Factories;

use App\Enums\SubjectModality;
use App\Enums\SubjectStatus;
use App\Models\Period;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'period_id' => Period::factory(),
            'name' => fake()->randomElement([
                'Matemáticas',
                'Programación',
                'Bases de Datos',
                'Física',
                'Química',
                'Estadística',
                'Inglés',
                'Metodología',
            ]),
            'professor' => fake()->optional()->name(),
            'credits' => fake()->numberBetween(1, 6),
            'modality' => fake()->randomElement(SubjectModality::cases()),
            'max_grade' => 20,
            'min_passing_grade' => 10,
            'final_grade' => null,
            'status' => SubjectStatus::Pending,
        ];
    }

    /**
     * Indicate that the subject has a final grade.
     */
    public function graded(float $grade): static
    {
        return $this->state(fn (array $attributes) => [
            'final_grade' => $grade,
        ]);
    }
}
