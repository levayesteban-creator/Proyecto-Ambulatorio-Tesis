<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SisDiagnosis;
use Illuminate\Database\Eloquent\Factories\Factory;

class SisDiagnosisFactory extends Factory
{
    protected $model = SisDiagnosis::class;

    public function definition(): array
    {
        static $index = 0;
        $diagnoses = [
            ['code' => 'I10', 'name' => 'Hipertensión Arterial Esencial'],
            ['code' => 'E11', 'name' => 'Diabetes Mellitus Tipo 2'],
            ['code' => 'J00', 'name' => 'Resfriado Común'],
        ];

        $diag = $diagnoses[$index++ % count($diagnoses)];
        return [
            'code' => $diag['code'],
            'name' => $diag['name'],
        ];
    }
}
