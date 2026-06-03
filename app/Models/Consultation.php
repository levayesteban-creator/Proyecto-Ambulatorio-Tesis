<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'age_at_moment',
        'address_at_moment',
        'phone_at_moment',
        'occupation_id',
        'reason_for_consultation',
        'current_illness',
        'consultation_type',
        'is_healthy',
        'therapeutic_plan',
        // Signos vitales
        'blood_pressure',
        'temperature',
        'temperature_route',       // Oral | Axilar | Rectal | Timpánica
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',       // SpO₂ %
        'weight',
        'height',
        'physical_examination',    // Texto libre legacy (mantener por compatibilidad)
        'complementary_studies',
        'epicrisis',
        'treatment_plan',
        'consultation_date',
    ];

    protected $casts = [
        'is_healthy'         => 'boolean',
        'temperature'        => 'decimal:1',
        'oxygen_saturation'  => 'decimal:1',
        'weight'             => 'decimal:2',
        'height'             => 'decimal:2',
        'consultation_date'  => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────────────
    // RELACIONES PRINCIPALES
    // ─────────────────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─────────────────────────────────────────────────────────────────────
    // EXAMEN FÍSICO ESTRUCTURADO (1:1)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Examen físico completo por secciones anatómicas (17 columnas JSON).
     * Relación 1:1 — un registro por consulta en consultation_physical_exams.
     */
    public function physicalExam(): HasOne
    {
        return $this->hasOne(ConsultationPhysicalExam::class, 'consultation_id');
    }

    // ─────────────────────────────────────────────────────────────────────
    // EXAMEN FUNCIONAL (1:1)
    // ─────────────────────────────────────────────────────────────────────

    public function functionalExam(): HasOne
    {
        return $this->hasOne(ConsultationFunctionalExam::class, 'consultation_id');
    }

    // ─────────────────────────────────────────────────────────────────────
    // RELACIONES DE DIAGNÓSTICOS (SIS)
    // ─────────────────────────────────────────────────────────────────────

    public function diagnoses(): BelongsToMany
    {
        return $this->belongsToMany(
            SisDiagnosis::class,
            'consultation_sis_diagnosis',
            'consultation_id',
            'sis_diagnosis_id'
        )
        ->withPivot([
            'id', 'diagnosis_type', 'unlisted_diagnosis',
            'medical_conduct_id', 'sort_order',
        ])
        ->withTimestamps()
        ->orderByPivot('sort_order');
    }

    public function sisDiagnoses(): HasMany
    {
        return $this->hasMany(ConsultationDiagnosis::class, 'consultation_id')
                    ->orderBy('sort_order');
    }

    // ─────────────────────────────────────────────────────────────────────
    // REFERENCIAS / CONTRA-REFERENCIAS
    // ─────────────────────────────────────────────────────────────────────

    public function referrals(): HasMany
    {
        return $this->hasMany(ConsultationReferral::class, 'consultation_id');
    }

    // ─────────────────────────────────────────────────────────────────────
    // SCOPES EPIDEMIOLÓGICOS (para reportes EPI)
    // ─────────────────────────────────────────────────────────────────────

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('created_at', $year)
                     ->whereMonth('created_at', $month);
    }

    public function scopePrimeraVez($query)
    {
        return $query->where('consultation_type', 'P');
    }

    public function scopeSucesiva($query)
    {
        return $query->where('consultation_type', 'S');
    }
}
