<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PsychobiologicalHabit
 *
 * Almacena los hábitos psicobiológicos y condiciones de vivienda del paciente.
 * Cada hábito es una columna JSON que el ORM deserializa automáticamente a array.
 *
 * CORRECCIÓN:
 * Modelo reescrito para coincidir con la nueva migración de columnas JSON.
 * Anteriormente tenía $fillable con campos planos (smoking, alcoholism)
 * que no coincidían con ninguna columna real de la tabla.
 */
class PsychobiologicalHabit extends Model
{
    use HasFactory;

    protected $table = 'psychobiological_habits';

    protected $fillable = [
        'patient_id',
        'alcohol',
        'tobacco',
        'coffee',
        'drugs',
        'physical_activity',
        'sleep',
        'nutrition',
        'sexual_habits',
        'gastrointestinal',
        'genitourinary',
        'housing',
    ];

    /**
     * Laravel deserializa automáticamente las columnas JSON a arrays PHP
     * al leerlas, y las serializa al guardarlas.
     * El frontend recibe objetos JavaScript nativos sin conversión adicional.
     */
    protected $casts = [
        'alcohol'          => 'array',
        'tobacco'          => 'array',
        'coffee'           => 'array',
        'drugs'            => 'array',
        'physical_activity'=> 'array',
        'sleep'            => 'array',
        'nutrition'        => 'array',
        'sexual_habits'    => 'array',
        'gastrointestinal' => 'array',
        'genitourinary'    => 'array',
        'housing'          => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
