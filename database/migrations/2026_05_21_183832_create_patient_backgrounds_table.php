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
        Schema::create('patient_backgrounds', function (Blueprint $table) {
            $table->id();

            // Relación estricta 1 a 1 con la Historia Clínica / Paciente
            $table->foreignId('patient_id')->unique()->constrained()->cascadeOnDelete();

            // 1. ANTECEDENTES ALÉRGICOS
            $table->boolean('allergies_deny')->default(true);
            $table->string('allergies_description')->nullable();

            // 2. ANTECEDENTES PATOLÓGICOS (Enfermedades Crónicas)
            $table->boolean('pathological_deny')->default(true);
            $table->string('pathological_disease')->nullable();
            $table->integer('pathological_onset_value')->nullable();
            $table->string('pathological_onset_unit')->nullable()->comment('días, meses, años');
            $table->boolean('pathological_controlled')->default(true);
            $table->string('pathological_treatment')->nullable();

            // 3. ANTECEDENTES INFECTOCONTAGIOSOS
            $table->boolean('infectious_deny')->default(true);
            $table->string('infectious_disease')->nullable();
            $table->integer('infectious_age')->nullable();
            $table->string('infectious_treatment')->nullable();
            $table->boolean('infectious_hospitalization')->default(false);
            $table->string('infectious_complications')->nullable();

            // 4. ANTECEDENTES INMUNOLÓGICOS (Esquema de Vacunación)
            $table->boolean('immune_deny_vaccination')->default(false);
            $table->string('immune_childhood_status')->default('completa')->comment('completa, incompleta, niega');
            $table->string('immune_missing_vaccines')->nullable();
            $table->string('immune_adult_vaccines')->nullable();
            $table->integer('immune_adult_age')->nullable();
            $table->string('immune_complications')->nullable();

            // 5. ANTECEDENTES TRANSFUSIONALES
            $table->boolean('transfusion_deny')->default(true);
            $table->integer('transfusion_age')->nullable();
            $table->string('transfusion_type')->nullable();
            $table->integer('transfusion_bags_count')->nullable();
            $table->string('transfusion_reason')->nullable();

            // 6. ANTECEDENTES GINECO-OBSTÉTRICOS (Soportan nulos si el paciente es masculino)
            $table->boolean('obgyn_apply')->default(true);
            $table->string('obgyn_gestas', 10)->nullable()->comment('Soporta números romanos: I, II, III');
            $table->string('obgyn_partos', 10)->nullable();
            $table->string('obgyn_cesareas', 10)->nullable();
            $table->string('obgyn_abortos', 10)->nullable();
            $table->string('obgyn_menarche')->nullable()->comment('Edad de primera menstruación');
            $table->string('obgyn_menopause')->nullable()->comment('Edad de menopausia o niega');
            $table->string('obgyn_cycle_periodicity')->nullable()->comment('Ej: Regular, Irregular');
            $table->string('obgyn_cycle_duration')->nullable()->comment('Duración en días');
            $table->integer('obgyn_cycle_pads_per_day')->nullable()->comment('Cantidad de compresas diarias');
            $table->date('obgyn_fur')->nullable()->comment('Fecha de Última Regla / FUM');

            // 7. ANTECEDENTES QUIRÚRGICOS
            $table->boolean('surgical_deny')->default(true);
            $table->string('surgical_intervention')->nullable();
            $table->integer('surgical_age')->nullable();
            $table->string('surgical_complications')->nullable();

            // 8. ANTECEDENTES TRAUMÁTICOS
            $table->boolean('traumatic_deny')->default(true);
            $table->string('traumatic_fracture')->nullable();
            $table->integer('traumatic_age')->nullable();
            $table->string('traumatic_treatment')->nullable();
            $table->string('traumatic_complications')->nullable();

            // 9. ANTECEDENTES DE ITS (Enfermedades de Transmisión Sexual)
            $table->boolean('std_deny')->default(true);
            $table->string('std_disease')->nullable();
            $table->integer('std_age')->nullable();
            $table->string('std_treatment')->nullable();
            $table->boolean('std_hospitalization')->default(false);
            $table->string('std_complications')->nullable();

            // 10. ANTECEDENTES EPIDEMIOLÓGICOS (Estadísticas epidemiológicas de viaje)
            $table->boolean('epidemiological_deny')->default(true);
            $table->string('epidem_destination')->nullable();
            $table->date('epidem_start_date')->nullable();
            $table->date('epidem_end_date')->nullable();
            $table->string('epidem_biome')->nullable()->comment('Ej: río, selva, playa');

            // 11. DISCAPACIDADES (Mapeado directo según requerimiento oficial)
            $table->boolean('disability_deny')->default(true);
            $table->tinyInteger('disability_type')->nullable()->comment('Mapeo del catálogo de discapacidades');
            $table->string('disability_specific_name')->nullable();
            $table->integer('disability_onset_value')->nullable();
            $table->string('disability_onset_unit')->nullable()->comment('días, meses, años');
            $table->string('disability_pharmacological_treatment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_backgrounds');
    }
};
