<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REESCRITURA COMPLETA — family_backgrounds
     *
     * PROBLEMA ORIGINAL:
     * La migración anterior tenía UNA sola columna JSON llamada 'relatives_data'.
     * Sin embargo, el modelo FamilyBackground define 8 columnas separadas con
     * cast 'array': mother, father, grandmother_maternal, grandfather_maternal,
     * grandmother_paternal, grandfather_paternal, siblings, children.
     * Al no existir esas columnas en la tabla, cualquier lectura/escritura reventaba.
     *
     * SOLUCIÓN:
     * Se crean las 8 columnas JSON independientes que el modelo ya espera.
     * Cada columna almacena el estado de un familiar con la estructura:
     * { unknown: bool, status: 'vivo'|'fallecido', age: int|null, pathology: string }
     *
     * Hermanos e Hijos extienden la estructura con:
     * { not_apply: bool, quantity: int, female_count: int, male_count: int, pathology: string }
     */
    public function up(): void
    {
        Schema::create('family_backgrounds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->unique()
                ->constrained('patients')
                ->cascadeOnDelete()
                ->comment('Relación 1:1 — cada paciente tiene un único bloque de antecedentes familiares');

            // ── LÍNEA MATERNA ─────────────────────────────────────────────
            $table->json('grandmother_maternal')
                ->nullable()
                ->comment('{ unknown, status: vivo|fallecido, age, pathology }');

            $table->json('grandfather_maternal')
                ->nullable()
                ->comment('{ unknown, status: vivo|fallecido, age, pathology }');

            // ── LÍNEA PATERNA ─────────────────────────────────────────────
            $table->json('grandmother_paternal')
                ->nullable()
                ->comment('{ unknown, status: vivo|fallecido, age, pathology }');

            $table->json('grandfather_paternal')
                ->nullable()
                ->comment('{ unknown, status: vivo|fallecido, age, pathology }');

            // ── PROGENITORES ──────────────────────────────────────────────
            $table->json('mother')
                ->nullable()
                ->comment('{ unknown, status: vivo|fallecido, age, pathology }');

            $table->json('father')
                ->nullable()
                ->comment('{ unknown, status: vivo|fallecido, age, pathology }');

            // ── COLATERALES E HIJOS ───────────────────────────────────────
            $table->json('siblings')
                ->nullable()
                ->comment('{ not_apply, quantity, female_count, male_count, pathology }');

            $table->json('children')
                ->nullable()
                ->comment('{ not_apply, quantity, female_count, male_count, pathology }');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_backgrounds');
    }
};
