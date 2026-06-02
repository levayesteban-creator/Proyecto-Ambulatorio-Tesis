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
        Schema::create('medical_conducts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Asegurada la unicidad del código
            $table->string('name')->comment('Nombre o descripción del plan (ej. Observación)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_conducts');
    }
};
