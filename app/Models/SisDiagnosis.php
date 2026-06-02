<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SisDiagnosis — Catálogo oficial de diagnósticos SIS/MPPS
 *
 * CORRECCIÓN PUNTO 3:
 * El archivo SisDiagnosis.php contenía 'class ConsultationDiagnosis' (nombre duplicado
 * que causaba un fatal error de PHP al cargar ambas clases). Corregido al nombre correcto.
 *
 * Tabla: sis_diagnoses
 * Campos: id | code (CIE-10/SIS) | name (descripción) | timestamps
 */
class SisDiagnosis extends Model
{
    protected $table = 'sis_diagnoses';

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Diagnósticos registrados en consultas que usaron esta patología del catálogo.
     */
    public function consultationDiagnoses(): HasMany
    {
        return $this->hasMany(ConsultationDiagnosis::class, 'sis_diagnosis_id');
    }
}
