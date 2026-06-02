<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Agregamos solo los campos nuevos que no estaban en tu migración original
            $table->boolean('is_healthy')->default(false)->after('consultation_type');
            $table->text('therapeutic_plan')->nullable()->after('diagnosis');

            // Opcional: Si quieres asegurar que respiratory_rate y height existan (ya que en tu código original no estaban)
            if (!Schema::hasColumn('consultations', 'respiratory_rate')) {
                $table->integer('respiratory_rate')->nullable()->after('heart_rate');
            }
            if (!Schema::hasColumn('consultations', 'height')) {
                $table->decimal('height', 5, 2)->nullable()->after('weight');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['is_healthy', 'therapeutic_plan', 'respiratory_rate', 'height']);
        });
    }
};
