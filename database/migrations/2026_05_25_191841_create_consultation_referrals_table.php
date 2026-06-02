<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_referrals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultation_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Almacenamos el código numérico de la especialidad (1 al 39) según la lista del ministerio
            $table->unsignedTinyInteger('specialty_code');

            // Para diferenciar si el registro es una "Referencia" o una "Contrarreferencia"
            $table->enum('type', ['referral', 'counter_referral']);

            $table->timestamps();

            // Evitamos duplicados: Una consulta no puede referenciar dos veces a la misma especialidad
            $table->unique(['consultation_id', 'specialty_code', 'type'], 'consultation_specialty_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_referrals');
    }
};
