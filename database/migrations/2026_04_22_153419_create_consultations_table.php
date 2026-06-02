<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            // Relaciones principales
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->comment('Médico que atiende');

            // 1. SNAPSHOT PATRONÍMICO (Estado al momento de esta consulta)
            $table->integer('age_at_moment');
            $table->string('address_at_moment');
            $table->string('phone_at_moment');
            $table->foreignId('occupation_id')->constrained();

            // 2. ANAMNESIS (La voz del paciente)
            $table->text('reason_for_consultation')->comment('Motivo de consulta');
            $table->text('current_illness')->nullable()->comment('Historial de la enfermedad actual');

            // 3. EXAMEN FÍSICO / SIGNOS VITALES (Objetivo)
            $table->string('blood_pressure')->nullable()->comment('Ej: 120/80');
            $table->decimal('temperature', 4, 1)->nullable()->comment('Ej: 36.5');
            $table->integer('heart_rate')->nullable()->comment('Ej: 80 bpm');
            $table->decimal('weight', 5, 2)->nullable()->comment('Ej: 75.50 kg');
            $table->text('physical_examination')->nullable()->comment('Hallazgos de la inspección');

            // 4. EXIGENCIAS EXCLUSIVAS DEL EPI-10 / PLANILLAS
            $table->enum('consultation_type', ['P', 'S', 'X'])->comment('P=Primera, S=Sucesiva, X=Asociado');

            // 5. CIERRE MÉDICO Y DIAGNÓSTICO (Análisis y Plan)
            $table->text('diagnosis');
            $table->text('treatment_plan')->comment('Indicaciones, recetas, conductas');

            // 6. CONTROL DE SEGURIDAD
            $table->boolean('is_verified')->default(false)->comment('Si es true, la historia es de solo lectura');

            // Metadatos
            $table->dateTime('consultation_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
