<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Religion;
use App\Models\InstructionLevel;
use App\Models\Occupation;
use App\Models\MaritalStatus;
use App\Models\Ethnicity;

class DatabaseLookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Estado Civil (Combinado con los requerimientos de la doctora y opciones estándar)
        $estados = ['Soltero', 'Casado', 'Viudo', 'Divorciado', 'Separado', 'Concubinato'];
        foreach ($estados as $e) {
            MaritalStatus::firstOrCreate(['name' => $e]);
        }

        // 2. Grado de Instrucción Numerado (Adaptado a la migración con columna 'code')
        $niveles = [
            1 => 'Inicial',
            2 => 'Primaria',
            3 => 'Secundaria',
            4 => 'Técnico medio',
            5 => 'Educación especial',
            6 => 'Universitaria',
            7 => 'Ninguno'
        ];
        foreach ($niveles as $code => $name) {
            InstructionLevel::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }

        // 3. Etnias (Las 48 opciones oficiales adaptadas a la migración con columna 'code')
        $etnias = [
            1 => 'Akawaio', 2 => 'Añú [Paraujano]', 3 => 'Arawak [Lokono]', 4 => 'Ayamán',
            5 => 'Baniwa', 6 => 'Baré', 7 => 'Barí', 8 => 'Catmensa', 9 => 'Chaima', 10 => 'Chiriana',
            11 => 'Cubeo', 12 => 'Cumanagoto', 13 => 'Eñepá [Panare]', 14 => 'Gayón', 15 => 'Guanono',
            16 => 'Hoti', 17 => 'Inga', 18 => 'Japreria', 19 => 'Jivi [Guajibo, Amorua, Sikwani]', 20 => 'Kariña',
            21 => 'Kuiba', 22 => 'Kurripako [Baniwa, Wakuénai]', 23 => 'Mako', 24 => 'Mapoyo [Wanai]', 25 => 'Pemón [Taurepan, Arekuna, Kamarakoto]',
            26 => 'Piapoco [Chase]', 27 => 'Piaroa [Wotjuja]', 28 => 'Puinave', 29 => 'Pumé [Yaruro]', 30 => 'Putumayo',
            31 => 'Sáliva', 32 => 'Sanemá [Sanuma]', 33 => 'Sape', 34 => 'Timoto Cuicas', 35 => 'Tomusa',
            36 => 'Uruak [Arutani]', 37 => 'Warao', 38 => 'Warekena', 39 => 'Wayúu [Guajiro]', 40 => 'Yabarana',
            41 => 'Yanomami [Shiriana, Guaica, Waika]', 42 => 'Yek’uana [Makiritare]', 43 => 'Yeral [Flengatu]', 44 => 'Yukpa',
            45 => 'Blanco o criollo', 46 => 'Afrodescendiente', 47 => 'Mestizo', 48 => 'Otros'
        ];
        foreach ($etnias as $code => $name) {
            Ethnicity::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }

        // 4. Religiones (Mantenidas de tu estructura)
        $religiones = ['Testigos de Jehová', 'Evangélicos', 'Católicos', 'Ateo', 'Otro'];
        foreach ($religiones as $name) {
            Religion::firstOrCreate(['name' => $name]);
        }

        // 5. Ocupaciones iniciales (Mantenidas de tu estructura)
        $ocupaciones = ['Desempleado', 'Obrero', 'Estudiante', 'Jubilado', 'Otro'];
        foreach ($ocupaciones as $name) {
            Occupation::firstOrCreate(['name' => $name]);
        }
    }
}
