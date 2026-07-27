<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'patient_backgrounds',
            'family_backgrounds',
            'psychobiological_habits',
            'consultation_physical_exams',
            'consultation_functional_exams',
            'consultation_sis_diagnosis',
            'consultation_referrals',
            'patient_extra_backgrounds',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'patient_backgrounds',
            'family_backgrounds',
            'psychobiological_habits',
            'consultation_physical_exams',
            'consultation_functional_exams',
            'consultation_sis_diagnosis',
            'consultation_referrals',
            'patient_extra_backgrounds',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
