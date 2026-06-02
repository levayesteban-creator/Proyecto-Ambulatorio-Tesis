<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MIGRACIÓN ANULADA — personal_backgrounds
     *
     * Esta migración existía como primer diseño de los antecedentes personales.
     * Fue reemplazada completamente por patient_backgrounds (migración 2026_05_21_183832)
     * que tiene la estructura completa de 11 bloques clínicos exigidos por el MPPS.
     *
     * La tabla 'personal_backgrounds' es una tabla huérfana:
     * - Ningún modelo la usa
     * - Ningún controlador la referencia
     * - Ninguna clave foránea apunta a ella
     *
     * Se mantiene el archivo para conservar el historial de migraciones,
     * pero el up() no crea ninguna tabla.
     */
    public function up(): void
    {
        // Intencionalmente vacío — tabla reemplazada por patient_backgrounds
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_backgrounds');
    }
};
