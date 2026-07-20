<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Religion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReligionFactory extends Factory
{
    protected $model = Religion::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Católico', 'Evangélico', 'Testigos de Jehová', 'Ateo', 'Otro']),
        ];
    }
}
