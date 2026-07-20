<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite no soporta ->change(). Solo ejecutamos en MySQL/PostgreSQL.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('consultations', function (Blueprint $table) {
            $table->string('address_at_moment')->nullable()->change();
            $table->string('phone_at_moment')->nullable()->change();
            $table->foreignId('occupation_id')->nullable()->change();
            $table->text('diagnosis')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('consultations', function (Blueprint $table) {
            $table->string('address_at_moment')->nullable(false)->change();
            $table->string('phone_at_moment')->nullable(false)->change();
            $table->foreignId('occupation_id')->nullable(false)->change();
            $table->text('diagnosis')->nullable(false)->change();
        });
    }
};
