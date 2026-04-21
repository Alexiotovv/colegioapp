<?php
// database/migrations/2025_01_01_000006_create_alumnos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_estudiante', 20)->unique();
            $table->string('dni', 8)->unique();
            $table->string('nombres', 60);
            $table->string('apellido_paterno', 60);
            $table->string('apellido_materno', 60);
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['M', 'F']);
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'retirado', 'egresado'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('dni');
            $table->index('codigo_estudiante');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};