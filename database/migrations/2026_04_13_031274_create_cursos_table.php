<?php
// database/migrations/2026_04_17_000001_create_cursos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('tipo', 60)->nullable(); // AREA, TALLER, TUTORIA
            $table->integer('horas_semanales')->default(0);
            $table->integer('orden')->default(0);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            
            // Nuevas relaciones
            $table->foreignId('nivel_id')->constrained('niveles')->onDelete('restrict');
            $table->foreignId('anio_academico_id')->constrained('anio_academicos')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['nivel_id', 'anio_academico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};