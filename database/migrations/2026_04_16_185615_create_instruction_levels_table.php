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
        Schema::create('instruction_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique()->comment('Código jerárquico del nivel de instrucción');
            $table->string('name')->unique()->comment('Nombre del nivel (Analfabeta, Primaria, TSU, etc.)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instruction_levels');
    }
};
