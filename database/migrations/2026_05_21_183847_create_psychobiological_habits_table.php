<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REESCRITURA COMPLETA — psychobiological_habits
     *
     * PROBLEMA ORIGINAL:
     * La migración anterior tenía columnas VARCHAR planas como 'alcohol_consumption'
     * y 'tobacco_consumption'. El PatientController concatenaba los datos del Vue
     * en strings como "Desde los 18 años. Tipo: ron. Cantidad: 200ml." perdiendo
     * toda la estructura — imposible recuperar campos individuales para edición
     * o cálculos estadísticos.
     *
     * SOLUCIÓN:
     * Cada hábito se almacena como columna JSON. Laravel deserializa automáticamente
     * los JSON a arrays PHP mediante $casts, manteniendo la estructura exacta que
     * envía el Vue y valida el StorePatientRequest.
     *
     * ESTRUCTURA JSON POR COLUMNA (mapeada 1:1 con el formulario Vue):
     *
     * alcohol:         { deny, start_age, end_age, type, quantity_ml, frequency_days, gets_drunk }
     * tobacco:         { deny, start_age, end_age, cigarettes_per_day, boxes_per_year }
     * coffee:          { deny, start_age, end_age, quantity_ml, type }
     * drugs:           { deny, start_age, end_age, route, frequency_per_day }
     * physical:        { type, times_per_week, minutes_per_day }
     * sleep:           { type, hours, frequency_per_day, interrupted, medication, siesta_duration_min, siesta_frequency_per_day }
     * nutrition:       { type, predominance_description, meals_count, appetite }
     * sexual:          { active, sexarche_age, partners_count, orientation, frequency_per_week, contraceptive_method }
     * gastrointestinal:{ evacuations_count, frequency_unit, color, odor, bristol_scale, shape }
     * genitourinary:   { urinations_count, color, odor, predominance }
     * housing:         { floor_material, roof_material, walls_material, rooms_count, habitants_count,
     *                    services: { water, electricity, gas, waste_collection },
     *                    animals: { quantity, intradomiciliary, extradomiciliary } }
     */
    public function up(): void
    {
        Schema::create('psychobiological_habits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->unique()
                ->constrained('patients')
                ->cascadeOnDelete()
                ->comment('Relación 1:1 — cada paciente tiene un solo bloque de hábitos');

            // ── HÁBITOS DE CONSUMO ────────────────────────────────────────
            $table->json('alcohol')
                ->nullable()
                ->comment('{ deny, start_age, end_age, type, quantity_ml, frequency_days, gets_drunk }');

            $table->json('tobacco')
                ->nullable()
                ->comment('{ deny, start_age, end_age, cigarettes_per_day, boxes_per_year }');

            $table->json('coffee')
                ->nullable()
                ->comment('{ deny, start_age, end_age, quantity_ml, type }');

            $table->json('drugs')
                ->nullable()
                ->comment('{ deny, start_age, end_age, route, frequency_per_day }');

            // ── ESTILO DE VIDA ────────────────────────────────────────────
            $table->json('physical_activity')
                ->nullable()
                ->comment('{ type, times_per_week, minutes_per_day }');

            $table->json('sleep')
                ->nullable()
                ->comment('{ type, hours, frequency_per_day, interrupted, medication, siesta_duration_min, siesta_frequency_per_day }');

            $table->json('nutrition')
                ->nullable()
                ->comment('{ type, predominance_description, meals_count, appetite }');

            $table->json('sexual_habits')
                ->nullable()
                ->comment('{ active, sexarche_age, partners_count, orientation, frequency_per_week, contraceptive_method }');

            // ── SISTEMAS FISIOLÓGICOS ─────────────────────────────────────
            $table->json('gastrointestinal')
                ->nullable()
                ->comment('{ evacuations_count, frequency_unit, color, odor, bristol_scale, shape }');

            $table->json('genitourinary')
                ->nullable()
                ->comment('{ urinations_count, color, odor, predominance }');

            // ── CONDICIONES DE VIVIENDA Y ENTORNO ────────────────────────
            $table->json('housing')
                ->nullable()
                ->comment('{ floor_material, roof_material, walls_material, rooms_count, habitants_count, services: { water, electricity, gas, waste_collection }, animals: { quantity, type_location } }');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychobiological_habits');
    }
};
