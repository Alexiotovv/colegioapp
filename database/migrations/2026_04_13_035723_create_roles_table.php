<?php
// database/migrations/2026_04_13_000001_create_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar roles por defecto
        DB::table('roles')->insert([
            ['nombre' => 'admin', 'descripcion' => 'Administrador del sistema', 'created_at' => now()],
            ['nombre' => 'director', 'descripcion' => 'Director del colegio', 'created_at' => now()],
            ['nombre' => 'docente', 'descripcion' => 'Docente', 'created_at' => now()],
            ['nombre' => 'apoderado', 'descripcion' => 'Apoderado/Padre de familia', 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};