<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * HISTORIAL DE CORRECCIONES:
     * - Unificado 'names' + 'surnames' → 'full_name' (alineado con PatientController, StorePatientRequest y Patient model)
     * - Reemplazado 'birth_country' + 'birth_state' + 'birth_city' → 'birth_place' (campo consolidado que usa el sistema)
     * - Añadida columna 'addr_zip_code' que faltaba (validada en StorePatientRequest)
     * - Cambiado 'residence_years' (integer) → 'residence_time' (string) para permitir valores como "5 años", "6 meses"
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            // ──────────────────────────────────────────────────────────────
            // IDENTIDAD BÁSICA (Formato Oficial MPPS)
            // ──────────────────────────────────────────────────────────────

            // FIX: era 'names' + 'surnames' (separados). El sistema usa 'full_name' como campo único.
            $table->string('full_name')->comment('Nombre completo del paciente tal como aparece en la cédula');

            $table->string('id_number')->unique()->comment('Cédula de identidad sin prefijo de nacionalidad');
            $table->char('nationality', 1)->default('V')->comment('V = Venezolano | E = Extranjero');
            $table->date('birth_date')->comment('Fecha de nacimiento para cálculo dinámico de edad');
            $table->enum('gender', ['F', 'M', 'O'])->default('O')->comment('F = Femenino | M = Masculino | O = Otro');

            // ──────────────────────────────────────────────────────────────
            // LUGAR DE NACIMIENTO
            // FIX: era 'birth_country' + 'birth_state' + 'birth_city' (separados).
            //      El sistema usa 'birth_place' como campo libre consolidado.
            // ──────────────────────────────────────────────────────────────
            $table->string('birth_place')->comment('Lugar de nacimiento: ej. "Puerto La Cruz, Anzoátegui, Venezuela"');

            // ──────────────────────────────────────────────────────────────
            // PERFIL SOCIO-DEMOGRÁFICO (Normalización Relacional)
            // ──────────────────────────────────────────────────────────────
            $table->foreignId('marital_status_id')->constrained('marital_statuses')
                ->comment('FK → marital_statuses');
            $table->foreignId('ethnicity_id')->constrained('ethnicities')
                ->comment('FK → ethnicities (48 pueblos según MPPS)');
            $table->foreignId('religion_id')->constrained('religions')
                ->comment('FK → religions');
            $table->foreignId('occupation_id')->constrained('occupations')
                ->comment('FK → occupations');
            $table->foreignId('instruction_level_id')->constrained('instruction_levels')
                ->comment('FK → instruction_levels');

            // ──────────────────────────────────────────────────────────────
            // DATOS CLÍNICOS PRIMARIOS
            // ──────────────────────────────────────────────────────────────
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', 'Desconoce'])
                ->default('Desconoce')
                ->comment('Grupo sanguíneo. "Desconoce" cuando el paciente no lo sabe');
            $table->enum('rh_factor', ['+', '-'])
                ->nullable()
                ->comment('Factor Rh. NULL cuando blood_type = Desconoce');

            // ──────────────────────────────────────────────────────────────
            // CONTACTO Y DIRECCIÓN ESTRUCTURADA
            // (Permite filtros estadísticos geográficos EPI-12/EPI-15)
            // ──────────────────────────────────────────────────────────────
            $table->string('phone_number')->nullable()->comment('Teléfono principal de contacto');

            $table->string('addr_state')->nullable()->comment('Estado de residencia');
            $table->string('addr_municipality')->nullable()->comment('Municipio de residencia');
            $table->string('addr_parish')->nullable()->comment('Parroquia de residencia');
            $table->string('addr_locality')->nullable()->comment('Localidad o comunidad');
            $table->string('addr_sector')->nullable()->comment('Sector o urbanización');
            $table->string('addr_street')->nullable()->comment('Avenida / Calle / Callejón');
            $table->string('addr_house_number')->nullable()->comment('Número de Casa o Apartamento');

            // FIX: columna faltante — validada en StorePatientRequest pero ausente en la migración original
            $table->string('addr_zip_code', 20)->nullable()->comment('Código postal');

            $table->string('addr_reference')->nullable()->comment('Punto de referencia para ubicar la vivienda');

            // FIX: era 'residence_years' (integer). El sistema usa 'residence_time' (string)
            //      para permitir valores como "5 años", "8 meses", "toda la vida", etc.
            $table->string('residence_time', 100)->nullable()->comment('Tiempo de residencia en la dirección actual');

            // ──────────────────────────────────────────────────────────────
            // ÍNDICES DE BÚSQUEDA RÁPIDA
            // ──────────────────────────────────────────────────────────────
            $table->index('full_name',   'idx_patient_full_name');
            $table->index('id_number',   'idx_patient_id_number');
            $table->index('birth_date',  'idx_patient_birth_date');
            $table->index('gender',      'idx_patient_gender');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
