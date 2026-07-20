<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MedicalConduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalConductFactory extends Factory
{
    protected $model = MedicalConduct::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('##'),
            'name' => fake()->sentence(3),
        ];
    }
}
