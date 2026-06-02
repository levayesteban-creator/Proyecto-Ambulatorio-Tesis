<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ConsultationDiagnosis — Pivote del sistema de diagnósticos SIS/MPPS
 *
 * CORRECCIONES PUNTO 3:
 * 1. Tabla corregida: apunta a 'consultation_sis_diagnosis' (la tabla activa del sistema).
 * 2. $fillable alineado exactamente con:
 *    - La migración 2026_05_25_200000_create_consultation_sis_diagnosis_table
 *    - StoreConsultationRequest (campos validados)
 *    - El formulario Vue Consultations/Create.vue (campos enviados)
 * 3. Relación con SisDiagnosis (catálogo SIS) y MedicalConduct (conducta médica).
 */
class ConsultationDiagnosis extends Model
{
    protected $table = 'consultation_sis_diagnosis';

    protected $fillable = [
        'consultation_id',
        'sis_diagnosis_id',       // FK → sis_diagnoses (nullable: puede usarse unlisted_diagnosis)
        'unlisted_diagnosis',      // Texto libre cuando la patología no está en el catálogo SIS
        'diagnosis_type',          // Sospechoso | Probable | Confirmado
        'medical_conduct_id',      // FK → medical_conducts (nullable)
        'sort_order',              // 1 = principal, 2 = secundario... calculado en el frontend
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Consulta médica a la que pertenece este diagnóstico.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Diagnóstico del catálogo oficial SIS/MPPS.
     * Puede ser null si el médico usó texto libre (unlisted_diagnosis).
     */
    public function sisDiagnosis(): BelongsTo
    {
        return $this->belongsTo(SisDiagnosis::class, 'sis_diagnosis_id');
    }

    /**
     * Conducta o plan médico tomado para este diagnóstico.
     */
    public function medicalConduct(): BelongsTo
    {
        return $this->belongsTo(MedicalConduct::class);
    }
}
