<?php
// database/migrations/2025_01_01_000010_create_matriculas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('apoderado_id')->nullable()->constrained('apoderados')->onDelete('set null');
            $table->foreignId('aula_id')->constrained('aulas')->onDelete('restrict');
            $table->date('fecha_matricula');
            $table->enum('estado', ['activa', 'retirada', 'culminada'])->default('activa');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->unique(['alumno_id', 'aula_id'], 'unique_matricula_alumno_aula');
            $table->index('aula_id');
            $table->index('alumno_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};