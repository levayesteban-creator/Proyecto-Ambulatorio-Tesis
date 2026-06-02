<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Código CIE-10 / SIS');
            $table->string('name')->comment('Descripción de la patología');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_diagnoses');
    }
};
