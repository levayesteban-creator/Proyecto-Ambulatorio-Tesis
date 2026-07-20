<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Occupation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OccupationFactory extends Factory
{
    protected $model = Occupation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Empleado', 'Obrero', 'Estudiante', 'Jubilado', 'Desempleado']),
        ];
    }
}
