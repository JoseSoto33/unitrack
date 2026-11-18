<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Career>
 */
class CareerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'university_id' => University::factory(),
            'title' => fake()->randomElement([
                'Ingeniería Informática',
                'Medicina',
                'Derecho',
                'Administración',
                'Psicología',
                'Arquitectura',
                'Economía',
                'Enfermería',
            ]),
            'created_by' => User::factory(),
        ];
    }
}
