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
        Schema::table('patients', function (Blueprint $table) {
            $table->index('full_name', 'idx_patients_full_name');
            $table->index('id_number', 'idx_patients_id_number');
            $table->index('birth_date', 'idx_patients_birth_date');
            $table->index('closed_at', 'idx_patients_closed_at');
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->index('consultation_date', 'idx_consultations_date');
            $table->index('consultation_type', 'idx_consultations_type');
            $table->index('is_healthy', 'idx_consultations_healthy');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_full_name');
            $table->dropIndex('idx_patients_id_number');
            $table->dropIndex('idx_patients_birth_date');
            $table->dropIndex('idx_patients_closed_at');
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex('idx_consultations_date');
            $table->dropIndex('idx_consultations_type');
            $table->dropIndex('idx_consultations_healthy');
        });
    }
};
