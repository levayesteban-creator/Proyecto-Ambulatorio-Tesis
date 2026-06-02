<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PUNTO 3 — Creación de la tabla pivote del sistema SIS/MPPS.
     *
     * Esta tabla es la que usa ConsultationDiagnosis model (y la relación
     * sisDiagnoses() de Consultation) para persistir los diagnósticos
     * registrados en el formulario de consulta médica.
     *
     * DIFERENCIA con consultation_diagnoses (tabla antigua/CIE-10):
     * - Usa sis_diagnosis_id → FK a sis_diagnoses (catálogo SIS del MPPS)
     * - medical_conduct_id  → FK a medical_conducts (nullable, no rompe al omitirse)
     * - sort_order          → campo de ordenamiento (no order_index)
     * - unlisted_diagnosis  → texto libre para enfermedades fuera del catálogo SIS
     */
    public function up(): void
    {
        Schema::create('consultation_sis_diagnosis', function (Blueprint $table) {
            $table->id();

            // ── Relación principal ────────────────────────────────────────
            $table->foreignId('consultation_id')
                ->constrained('consultations')
                ->cascadeOnDelete()
                ->comment('Consulta a la que pertenece este diagnóstico');

            // ── Diagnóstico del catálogo SIS ──────────────────────────────
            $table->foreignId('sis_diagnosis_id')
                ->nullable()
                ->constrained('sis_diagnoses')
                ->nullOnDelete()
                ->comment('FK al catálogo oficial SIS/MPPS. NULL cuando se usa texto libre');

            // ── Diagnóstico no listado (texto libre) ──────────────────────
            $table->string('unlisted_diagnosis')->nullable()
                ->comment('Diagnóstico alternativo si la patología no aparece en el catálogo SIS');

            // ── Clasificación epidemiológica ──────────────────────────────
            $table->enum('diagnosis_type', ['Sospechoso', 'Probable', 'Confirmado'])
                ->default('Confirmado')
                ->comment('Nivel de certeza diagnóstica exigido por el EPI-10');

            // ── Conducta médica (FK, nullable para no bloquear el guardado) ─
            $table->foreignId('medical_conduct_id')
                ->nullable()
                ->constrained('medical_conducts')
                ->nullOnDelete()
                ->comment('Plan o conducta médica tomada para este diagnóstico específico');

            // ── Orden de prioridad clínica ────────────────────────────────
            $table->unsignedTinyInteger('sort_order')
                ->default(1)
                ->comment('1 = diagnóstico principal, 2 = secundario, etc. Se calcula en el frontend');

            // ── Índices para consultas estadísticas EPI ───────────────────
            $table->index(['consultation_id', 'sort_order'], 'idx_csd_consultation_order');
            $table->index('sis_diagnosis_id',                'idx_csd_sis_diagnosis');
            $table->index('diagnosis_type',                  'idx_csd_diagnosis_type');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_sis_diagnosis');
    }
};
