<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('epidemiological_diagnoses', function (Blueprint $table) {
            $table->id();
            // Campo 'code' unificado y único para validación de tesis
            $table->string('code', 10)->unique()->comment('Código único de identificación');

            $table->string('cie_code', 10)->nullable()->comment('Código Internacional CIE-10 (ej. J20)');
            $table->string('sis_code', 10)->nullable()->comment('Código SIS del MPPS (ej. 15)');
            $table->string('name')->comment('Nombre de la enfermedad (ej. Bronquitis Aguda)');
            $table->boolean('is_eno')->default(false)->comment('¿Es de Notificación Obligatoria para EPI-13?');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epidemiological_diagnoses');
    }
};
