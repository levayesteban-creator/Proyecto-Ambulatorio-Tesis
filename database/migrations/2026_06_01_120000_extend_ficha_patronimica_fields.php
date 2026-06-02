<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('nationality_country', 100)
                ->nullable()
                ->after('nationality')
                ->comment('Nacionalidad declarada (texto libre, ej. Venezolana)');

            $table->string('occupation_detail', 255)
                ->nullable()
                ->after('occupation_id')
                ->comment('Profesión u oficio cuando el catálogo es "Otro"');

            $table->string('religion_detail', 255)
                ->nullable()
                ->after('religion_id')
                ->comment('Religión cuando el catálogo es "Otro"');
        });

        Schema::table('patient_backgrounds', function (Blueprint $table) {
            $table->json('disability_types')
                ->nullable()
                ->after('disability_type')
                ->comment('Tipos de discapacidad 1–11 (multiselección)');
        });

        DB::table('patient_backgrounds')
            ->whereNotNull('disability_type')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('patient_backgrounds')
                        ->where('id', $row->id)
                        ->update([
                            'disability_types' => json_encode([(int) $row->disability_type]),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('patient_backgrounds', function (Blueprint $table) {
            $table->dropColumn('disability_types');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['nationality_country', 'occupation_detail', 'religion_detail']);
        });
    }
};
