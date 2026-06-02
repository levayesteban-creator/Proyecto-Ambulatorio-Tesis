<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (! Schema::hasColumn('consultations', 'complementary_studies')) {
                $table->text('complementary_studies')
                    ->nullable()
                    ->after('physical_examination')
                    ->comment('Exploración complementaria: laboratorio, imagenología, etc.');
            }
            if (! Schema::hasColumn('consultations', 'epicrisis')) {
                $table->text('epicrisis')
                    ->nullable()
                    ->after('treatment_plan')
                    ->comment('Resumen / epicrisis de la consulta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (Schema::hasColumn('consultations', 'complementary_studies')) {
                $table->dropColumn('complementary_studies');
            }
            if (Schema::hasColumn('consultations', 'epicrisis')) {
                $table->dropColumn('epicrisis');
            }
        });
    }
};
