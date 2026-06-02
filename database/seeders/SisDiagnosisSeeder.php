<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SisDiagnosisSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Desactivamos llaves foráneas para poder truncar la tabla de forma segura
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sis_diagnoses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // 2. Catálogo completo y categorizado
        $diagnoses = [
            // Crónicas / Metabólicas
            ['code' => 'I10',   'name' => 'Hipertensión Arterial Esencial (Primaria)'],
            ['code' => 'E11',   'name' => 'Diabetes Mellitus Tipo 2'],
            ['code' => 'E78',   'name' => 'Trastornos del Metabolismo de las Lipoproteínas (Dislipidemia)'],
            ['code' => 'E66',   'name' => 'Obesidad por Exceso de Calorías'],

            // Respiratorias
            ['code' => 'J00',   'name' => 'Rinofaringitis Aguda (Resfriado Común)'],
            ['code' => 'J20',   'name' => 'Bronquitis Aguda'],
            ['code' => 'J45',   'name' => 'Asma Bronquial'],
            ['code' => 'J02',   'name' => 'Faringitis Aguda'],

            // Infecciosas / Agudas / Endémicas
            ['code' => 'A09',   'name' => 'Gastroenteritis y Colitis de Origen Infeccioso (Diarrea Aguda)'],
            ['code' => 'B34',   'name' => 'Infección Viral de Sitio No Especificado (Virosis)'],
            ['code' => 'N39.0', 'name' => 'Infección de Vías Urinarias (Sitio No Especificado)'],
            ['code' => 'A90',   'name' => 'Dengue Clásico'],
            ['code' => 'A92.0', 'name' => 'Enfermedad por Virus Chikungunya'],
            ['code' => 'B35',   'name' => 'Dermatofitosis (Micosis Cutánea)'],

            // Síntomas / Signos
            ['code' => 'R50',   'name' => 'Fiebre de Origen Desconocido / Inespecífica'],
            ['code' => 'R51',   'name' => 'Cefalea / Dolor de Cabeza Inespecífico'],
            ['code' => 'M54.5', 'name' => 'Lumbago No Especificado (Lumbalgia Mecánica)'],
            ['code' => 'K30',   'name' => 'Dispepsia / Síndrome Dispéptico'],

            // Preventivos / Paciente Sano
            ['code' => 'Z00.0', 'name' => 'Examen Médico General / Control de Rutina (Adulto Sano)'],
            ['code' => 'Z00.1', 'name' => 'Control de Salud de Rutina del Niño (Niño Sano)'],
            ['code' => 'Z02.1', 'name' => 'Examen Médico para Aptitud Física / Preempleo'],
        ];

        // 3. Preparación de datos (agregando timestamps para cumplir con el estándar Laravel)
        $dataToInsert = array_map(function ($diag) use ($now) {
            return [
                'code'       => $diag['code'],
                'name'       => $diag['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $diagnoses);

        // 4. Inserción masiva única (Mucho más eficiente que un foreach con múltiples consultas)
        DB::table('sis_diagnoses')->insert($dataToInsert);
    }
}
