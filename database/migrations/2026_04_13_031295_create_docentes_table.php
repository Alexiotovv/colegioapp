<?php
// database/migrations/2025_01_01_000008_create_docentes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 50);
            $table->string('apellido_materno', 50);
            $table->string('especialidad', 100)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('email', 100)->unique();
            $table->date('fecha_ingreso')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'vacaciones'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};