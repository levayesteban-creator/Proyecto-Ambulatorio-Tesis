<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpidemiologicalDiagnosis extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     * Agregamos 'code' para corregir el error de la base de datos.
     */
    protected $fillable = [
        'code',
        'cie_code',
        'sis_code',
        'name',
        'is_eno',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'is_eno' => 'boolean',
        // Asegura que las fechas se manejen correctamente si las usas
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
