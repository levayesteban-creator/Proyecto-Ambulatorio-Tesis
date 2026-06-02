<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de diagnósticos del sistema CIE-10 original (diseño anterior).
     * Mantenida en el proyecto como referencia y para futuros reportes EPI.
     * El sistema activo usa consultation_sis_diagnosis (ver migración 2026_05_25_200000).
     *
     * FIX: medical_conduct_id era NOT NULL → cambiado a nullable para no
     * bloquear el migrate:fresh cuando no hay conducta médica registrada.
     */
    public function up(): void
    {
        Schema::create('consultation_diagnoses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('epidemiological_diagnosis_id')
                ->nullable()
                ->constrained();

            $table->string('unlisted_diagnosis')->nullable()
                ->comment('Se usa si el diagnóstico no está en el catálogo CIE-10');

            $table->enum('diagnosis_type', ['Sospechoso', 'Probable', 'Confirmado', 'No Aplica'])
                ->default('Confirmado');

            // FIX: era ->constrained() sin nullable → reventaba migrate:fresh
            $table->foreignId('medical_conduct_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedTinyInteger('order_index')->default(0)
                ->comment('Prioridad clínica del diagnóstico CIE-10');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_diagnoses');
    }
};
