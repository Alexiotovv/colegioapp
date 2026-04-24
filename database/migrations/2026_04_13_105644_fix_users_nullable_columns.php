<?php
// database/migrations/2026_04_13_999999_fix_users_nullable_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modificar columnas para que acepten NULL
            DB::statement('ALTER TABLE users MODIFY userable_type VARCHAR(255) NULL');
            DB::statement('ALTER TABLE users MODIFY userable_id BIGINT UNSIGNED NULL');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement('ALTER TABLE users MODIFY userable_type VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE users MODIFY userable_id BIGINT UNSIGNED NOT NULL');

        });
    }
};