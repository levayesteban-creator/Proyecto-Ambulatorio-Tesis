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
            // Drop duplicate indexes created by add_search_indexes migration
            // These are duplicates of idx_patient_full_name, idx_patient_id_number, idx_patient_birth_date
            $table->dropIndex('idx_patients_full_name');
            $table->dropIndex('idx_patients_id_number');
            $table->dropIndex('idx_patients_birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Re-create the indexes that were dropped
            $table->index('full_name', 'idx_patients_full_name');
            $table->index('id_number', 'idx_patients_id_number');
            $table->index('birth_date', 'idx_patients_birth_date');
        });
    }
};
