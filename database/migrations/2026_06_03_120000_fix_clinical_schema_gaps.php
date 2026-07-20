<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (! Schema::hasColumn('consultations', 'temperature_route')) {
                $table->string('temperature_route', 20)
                    ->nullable()
                    ->after('temperature')
                    ->comment('Oral | Axilar | Rectal | Timpánica');
            }
            if (! Schema::hasColumn('consultations', 'oxygen_saturation')) {
                $table->decimal('oxygen_saturation', 4, 1)
                    ->nullable()
                    ->after('respiratory_rate')
                    ->comment('SpO₂ %');
            }
        });

        Schema::table('consultation_functional_exams', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_functional_exams', 'gastrointestinal_deny')) {
                $table->boolean('gastrointestinal_deny')
                    ->default(true)
                    ->after('cardiovascular_description');
            }
            if (! Schema::hasColumn('consultation_functional_exams', 'genitourinary_deny')) {
                $table->boolean('genitourinary_deny')
                    ->default(true)
                    ->after('gastrointestinal_description');
            }
        });

        if (Schema::hasTable('consultation_sis_diagnosis')
            && DB::connection()->getDriverName() !== 'sqlite'
        ) {
            DB::statement(
                "ALTER TABLE consultation_sis_diagnosis MODIFY diagnosis_type "
                . "ENUM('Sospechoso', 'Probable', 'Confirmado', 'No Aplica') NOT NULL DEFAULT 'Confirmado'"
            );
        }
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (Schema::hasColumn('consultations', 'temperature_route')) {
                $table->dropColumn('temperature_route');
            }
            if (Schema::hasColumn('consultations', 'oxygen_saturation')) {
                $table->dropColumn('oxygen_saturation');
            }
        });

        Schema::table('consultation_functional_exams', function (Blueprint $table) {
            if (Schema::hasColumn('consultation_functional_exams', 'gastrointestinal_deny')) {
                $table->dropColumn('gastrointestinal_deny');
            }
            if (Schema::hasColumn('consultation_functional_exams', 'genitourinary_deny')) {
                $table->dropColumn('genitourinary_deny');
            }
        });

        if (Schema::hasTable('consultation_sis_diagnosis')) {
            DB::statement(
                "ALTER TABLE consultation_sis_diagnosis MODIFY diagnosis_type "
                . "ENUM('Sospechoso', 'Probable', 'Confirmado') NOT NULL DEFAULT 'Confirmado'"
            );
        }
    }
};
