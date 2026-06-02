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
        Schema::create('ethnicities', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique()->comment('Código oficial de la etnia (1 al 48)');
            $table->string('name')->unique()->comment('Nombre de la etnia (ej. Wayúu, Yanomami, etc.)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ethnicities');
    }
};
