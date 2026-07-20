<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientBackground extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo (opcional, por claridad semántica).
     */
    protected $table = 'patient_backgrounds';

    /**
     * Atributos asignables de forma masiva (Mass Assignment)
     * Adaptados para recibir la estructura exacta que valida tu FormRequest.
     */
    protected $fillable = [
        'patient_id',

        // 1. Alérgicos
        'allergies_deny',
        'allergies_description',

        // 2. Patológicos (Campos individuales heredados)
        'pathological_deny',
        'pathological_disease',
        'pathological_onset_value',
        'pathological_onset_unit',
        'pathological_controlled',
        'pathological_treatment',

        // 3. Infectocontagiosos
        'infectious_deny',
        'infectious_disease',
        'infectious_age',
        'infectious_treatment',
        'infectious_hospitalization',
        'infectious_complications',

        // 4. Inmunológicos
        'immune_deny_vaccination',
        'immune_childhood_status',
        'immune_missing_vaccines',
        'immune_adult_vaccines',
        'immune_adult_age',
        'immune_complications',

        // 5. Transfusionales
        'transfusion_deny',
        'transfusion_age',
        'transfusion_type',
        'transfusion_bags_count',
        'transfusion_reason',

        // 6. Gineco-Obstétricos
        'obgyn_apply',
        'obgyn_gestas',
        'obgyn_partos',
        'obgyn_cesareas',
        'obgyn_abortos',
        'obgyn_menarche',
        'obgyn_menopause',
        'obgyn_cycle_periodicity',
        'obgyn_cycle_duration',
        'obgyn_cycle_pads_per_day',
        'obgyn_fur',

        // 7. Quirúrgicos
        'surgical_deny',
        'surgical_intervention',
        'surgical_age',
        'surgical_complications',

        // 8. Traumáticos
        'traumatic_deny',
        'traumatic_fracture',
        'traumatic_age',
        'traumatic_treatment',
        'traumatic_complications',

        // 9. ETS
        'std_deny',
        'std_disease',
        'std_age',
        'std_treatment',
        'std_hospitalization',
        'std_complications',

        // 10. Epidemiológicos
        'epidemiological_deny',
        'epidem_destination',
        'epidem_start_date',
        'epidem_end_date',
        'epidem_biome',

        // 11. Discapacidades
        'disability_deny',
        'disability_type',
        'disability_types',
        'disability_specific_name',
        'disability_onset_value',
        'disability_onset_unit',
        'disability_pharmacological_treatment',
    ];

    /**
     * Conversión de tipos nativos (Casts)
     * Maneja tanto los tipos booleanos/fechas como los arrays serializados en JSON.
     */
    protected $casts = [
        // Banderas lógicas de negación o estados binarios
        'allergies_deny'             => 'boolean',
        'pathological_deny'          => 'boolean',
        'pathological_controlled'    => 'boolean',
        'infectious_deny'            => 'boolean',
        'infectious_hospitalization' => 'boolean',
        'immune_deny_vaccination'    => 'boolean',
        'transfusion_deny'           => 'boolean',
        'obgyn_apply'                => 'boolean',
        'surgical_deny'              => 'boolean',
        'traumatic_deny'             => 'boolean',
        'std_deny'                   => 'boolean',
        'std_hospitalization'        => 'boolean',
        'epidemiological_deny'       => 'boolean',
        'disability_deny'            => 'boolean',
        'disability_types'           => 'array',

        // Tipos numéricos explícitos para cálculo clínico
        'obgyn_gestas'               => 'string',
        'obgyn_partos'               => 'string',
        'obgyn_cesareas'             => 'string',
        'obgyn_abortos'              => 'string',
        'obgyn_menarche'             => 'string',
        'obgyn_menopause'            => 'string',
        'obgyn_cycle_pads_per_day'   => 'integer',

        // Campos de tipo fecha nativa
        'obgyn_fur'                  => 'date',
        'epidem_start_date'          => 'date',
        'epidem_end_date'            => 'date',
    ];

    /**
     * Relación inversa hacia la Ficha Patronímica.
     * Cada registro de antecedentes pertenece de forma estricta a un paciente.
     */
    public function patient(): BelongsTo
    {
        // Aseguramos la relación apuntando explícitamente a la llave foránea nativa
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
