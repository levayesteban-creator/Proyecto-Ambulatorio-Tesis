<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EpidemiologicalDiagnosis;

class EpidemiologicalDiagnosisSeeder extends Seeder
{
    public function run(): void
    {
        $diagnoses = [
            ['code' => 'DENG-01', 'cie_code' => 'A90', 'sis_code' => '02', 'name' => 'Dengue sin signos de alarma', 'is_eno' => true],
            ['code' => 'MALA-01', 'cie_code' => 'B51', 'sis_code' => '70', 'name' => 'Malaria Vivax', 'is_eno' => true],
            ['code' => 'COLE-01', 'cie_code' => 'A00', 'sis_code' => '01', 'name' => 'Cólera', 'is_eno' => true],
            ['code' => 'GRIP-01', 'cie_code' => 'J11', 'sis_code' => '05', 'name' => 'Gripe común / Influenza', 'is_eno' => false],
            ['code' => 'BRON-01', 'cie_code' => 'J20', 'sis_code' => '15', 'name' => 'Bronquitis Aguda', 'is_eno' => false],
            ['code' => 'HIPT-01', 'cie_code' => 'I10', 'sis_code' => '77', 'name' => 'Hipertensión Arterial', 'is_eno' => false],
            ['code' => 'FRAC-01', 'cie_code' => 'S72', 'sis_code' => '114', 'name' => 'Fractura de fémur', 'is_eno' => false],
        ];

        foreach ($diagnoses as $diagnosis) {
            // Usamos 'code' como identificador único para el firstOrCreate
            EpidemiologicalDiagnosis::firstOrCreate(
                ['code' => $diagnosis['code']],
                $diagnosis
            );
        }
    }
}
