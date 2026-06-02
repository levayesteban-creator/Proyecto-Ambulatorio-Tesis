<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PUNTO 5 — Corrección de columnas NOT NULL en la tabla consultations.
     *
     * PROBLEMA DETECTADO:
     * Las siguientes columnas son NOT NULL en la migración original pero el
     * sistema no puede garantizar que siempre tengan valor:
     *
     * 1. address_at_moment → El Vue lee patient.current_address (accessor que no existía).
     *    Si el paciente no tiene dirección completa cargada, llega vacío y MySQL rechaza el INSERT.
     *
     * 2. phone_at_moment → El paciente puede no tener teléfono registrado (phone_number nullable
     *    en la tabla patients). Llegar vacío revienta la transacción.
     *
     * 3. occupation_id (FK) → La tabla patients tiene occupation_id como NOT NULL pero
     *    en el formulario de consulta el médico puede cambiarla o dejarla sin valor.
     *    Siendo FK, MySQL rechaza el INSERT si llega null.
     *
     * 4. diagnosis (TEXT NOT NULL legacy) → Esta columna existía en el diseño original
     *    pero el sistema actual usa la tabla consultation_sis_diagnosis para los diagnósticos.
     *    El Controller nunca llena esta columna → MySQL rechaza el INSERT en cada consulta.
     *
     * SOLUCIÓN: hacer nullable cada columna de forma no destructiva (sin tocar datos existentes).
     */
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {

            // 1. address_at_moment: nullable porque puede precargarse vacío
            //    si el paciente tiene dirección incompleta en su ficha
            $table->string('address_at_moment')->nullable()->change();

            // 2. phone_at_moment: nullable porque phone_number en patients es nullable
            $table->string('phone_at_moment')->nullable()->change();

            // 3. occupation_id: eliminar la FK NOT NULL y recriarla nullable
            //    Nota: en algunos motores hay que dropForeign antes de change()
            $table->foreignId('occupation_id')
                ->nullable()
                ->change();

            // 4. diagnosis: columna legacy del diseño anterior — se hace nullable
            //    para no romper el migrate:fresh. El sistema activo usa consultation_sis_diagnosis.
            //    No se elimina para preservar datos históricos si los hubiera.
            $table->text('diagnosis')->nullable()->change();
        });
    }

    /**
     * Revertir: restaurar las restricciones NOT NULL originales.
     * Solo usar en desarrollo — nunca en producción con datos reales.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('address_at_moment')->nullable(false)->change();
            $table->string('phone_at_moment')->nullable(false)->change();
            $table->foreignId('occupation_id')->nullable(false)->change();
            $table->text('diagnosis')->nullable(false)->change();
        });
    }
};
