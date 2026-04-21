<?php
// database/migrations/2026_04_13_000002_add_role_id_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->after('id')->default(4)->constrained('roles')->onDelete('restrict');
            $table->foreignId('docente_id')->nullable()->after('role_id')->constrained('docentes')->onDelete('set null');
            $table->foreignId('alumno_id')->nullable()->after('docente_id')->constrained('alumnos')->onDelete('set null');
            $table->foreignId('apoderado_id')->nullable()->after('alumno_id')->constrained('apoderados')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['docente_id']);
            $table->dropForeign(['alumno_id']);
            $table->dropForeign(['apoderado_id']);
            $table->dropColumn(['role_id', 'docente_id', 'alumno_id', 'apoderado_id']);
        });
    }
};