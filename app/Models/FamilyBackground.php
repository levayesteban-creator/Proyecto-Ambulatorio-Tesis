<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyBackground extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'family_backgrounds';

    /**
     * Atributos asignables de forma masiva (Mass Assignment).
     * Definidos para recibir los bloques JSON validados del FormRequest.
     */
    protected $fillable = [
        'patient_id',
        'mother',
        'father',
        'grandmother_maternal',
        'grandfather_maternal',
        'grandmother_paternal',
        'grandfather_paternal',
        'siblings',
        'children',
    ];

    /**
     * Conversión de tipos nativos (Casts).
     * Serializa automáticamente los arrays de PHP a JSON al guardar en Laragon,
     * y los deserializa a arrays nativos cuando los recuperas para Inertia/Vue.
     */
    protected $casts = [
        'mother'               => 'array',
        'father'               => 'array',
        'grandmother_maternal' => 'array',
        'grandfather_maternal' => 'array',
        'grandmother_paternal' => 'array',
        'grandfather_paternal' => 'array',
        'siblings'             => 'array',
        'children'             => 'array',
    ];

    /**
     * Relación inversa hacia la Ficha Patronímica (Patient).
     * Cada registro de antecedentes familiares pertenece estrictamente a un paciente.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
