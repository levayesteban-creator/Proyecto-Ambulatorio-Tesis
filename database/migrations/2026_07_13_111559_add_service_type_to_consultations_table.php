<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('service_type', 2)->default('MG')->after('consultation_type')->comment('MG=Medicina General, EP=Epidemiología, EM=Emergencia, PR=Preventiva/Programas, OT=Otra');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
