<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('epicrisis');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_at', 'verified_by']);
        });
    }
};
