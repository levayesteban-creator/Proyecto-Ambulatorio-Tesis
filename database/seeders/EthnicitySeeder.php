<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ethnicity;

class EthnicitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $etnias = [
            'Akawaio', 'Añú [Paraujano]', 'Arawak [Lokono]', 'Ayamán', 'Baniwa', 'Baré', 'Barí',
            'Catmensa', 'Chaima', 'Chiriana', 'Cubeo', 'Cumanagoto', 'Eñepá [Panare]', 'Gayón',
            'Guanono', 'Hoti', 'Inga', 'Japreria', 'Jivi', 'Kariña', 'Kuiba', 'Kurripako',
            'Mako', 'Mapoyo', 'Pemón', 'Piapoco', 'Piaroa', 'Puinave', 'Pumé', 'Putumayo',
            'Sáliva', 'Sanemá', 'Sape', 'Timoto Cuicas', 'Tomusa', 'Uruak', 'Warao',
            'Warekena', 'Wayúu [Guajiro]', 'Yabarana', 'Yanomami', 'Yek’uana', 'Yeral',
            'Yukpa', 'Blanco o criollo', 'Afrodescendiente', 'Mestizo', 'Otros'
        ];

        foreach ($etnias as $index => $etnia) {
            // Autogenera un código secuencial (ET-01, ET-02...) para evitar fallos si la columna 'code' es exigida por la migración
            $code = 'ET-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);

            Ethnicity::firstOrCreate(
                ['name' => $etnia],
                ['code' => $code]
            );
        }
    }
}
