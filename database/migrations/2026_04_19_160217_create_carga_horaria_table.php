<?php
// database/migrations/2026_04_19_000001_create_carga_horaria_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carga_horaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('aula_id')->constrained('aulas')->onDelete('cascade');
            $table->integer('horas_semanales')->default(0);
            $table->enum('dia_semana', ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'])->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->unique(['docente_id', 'curso_id', 'aula_id', 'dia_semana', 'hora_inicio'], 'unique_carga_horaria');
            $table->index(['curso_id', 'aula_id']);
            $table->index('dia_semana');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_horaria');
    }
};