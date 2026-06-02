<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos a todos los seeders en orden lógico
        $this->call([
            RoleSeeder::class,                 // Primero creamos los roles
            UserSeeder::class,                 // Luego los usuarios
            DatabaseLookupSeeder::class,       // Tablas maestras (ocupaciones, etc.)
            MedicalConductSeeder::class,       // Catálogo de conductas
            EpidemiologicalDiagnosisSeeder::class, // Catálogo EPI-12
            SisDiagnosisSeeder::class,         // <--- NUEVO: Catálogo SIS
        ]);
    }
}
