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
            $table->fullText('full_name', 'ft_patients_full_name');
            $table->fullText('id_number', 'ft_patients_id_number');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('ft_patients_full_name');
            $table->dropIndex('ft_patients_id_number');
        });
    }
};
