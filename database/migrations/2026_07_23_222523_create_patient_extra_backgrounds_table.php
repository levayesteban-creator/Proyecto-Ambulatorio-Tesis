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
        Schema::create('patient_extra_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('category')->comment('Categoría: pathological, surgical, infectious, allergic, transfusion, std, traumatic, epidemiological, disability, immunological, obgyn');
            $table->string('disease_name')->comment('Nombre de enfermedad, intervención o condición');
            $table->integer('onset_value')->nullable()->comment('Edad de inicio');
            $table->string('onset_unit', 20)->default('años')->comment('Unidad: años, meses, días');
            $table->text('treatment')->nullable()->comment('Tratamiento actual');
            $table->text('complications')->nullable()->comment('Complicaciones');
            $table->text('description')->nullable()->comment('Descripción libre adicional');
            $table->timestamps();

            $table->index(['patient_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_extra_backgrounds');
    }
};
