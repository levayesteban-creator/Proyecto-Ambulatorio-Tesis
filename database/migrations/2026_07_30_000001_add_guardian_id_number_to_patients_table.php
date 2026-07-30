<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('id_number', 20)->nullable()->change();
            $table->string('guardian_id_number', 20)->nullable()->after('id_number');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('id_number', 20)->nullable(false)->change();
            $table->dropColumn('guardian_id_number');
        });
    }
};
