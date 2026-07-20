<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InstructionLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstructionLevelFactory extends Factory
{
    protected $model = InstructionLevel::class;

    public function definition(): array
    {
        static $counter = 0;
        $levels = ['Inicial', 'Primaria', 'Secundaria', 'Universitaria'];

        return [
            'code' => (string) ($counter + 1),
            'name' => $levels[$counter++ % count($levels)],
        ];
    }
}
