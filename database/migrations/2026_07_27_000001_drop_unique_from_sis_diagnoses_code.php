<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sis_diagnoses', function ($table) {
            $table->dropUnique('sis_diagnoses_code_unique');
            $table->string('code', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sis_diagnoses', function ($table) {
            $table->string('code', 10)->nullable()->change();
            $table->unique('code');
        });
    }
};
