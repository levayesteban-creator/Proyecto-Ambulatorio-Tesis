<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ethnicity;
use Illuminate\Database\Eloquent\Factories\Factory;

class EthnicityFactory extends Factory
{
    protected $model = Ethnicity::class;

    public function definition(): array
    {
        static $counter = 0;
        $names = ['Mestizo', 'Blanco', 'Afrodescendiente', 'Indígena', 'Otro'];

        return [
            'code' => (string) ($counter + 1),
            'name' => $names[$counter++ % count($names)],
        ];
    }
}
