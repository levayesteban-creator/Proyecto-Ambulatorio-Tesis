<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MaritalStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaritalStatusFactory extends Factory
{
    protected $model = MaritalStatus::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Soltero', 'Casado', 'Viudo', 'Divorciado']),
        ];
    }
}
