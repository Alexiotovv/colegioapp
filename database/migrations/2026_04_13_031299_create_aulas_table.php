<?php
// database/migrations/2026_04_18_000001_create_aulas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->enum('turno', ['MAÑANA', 'TARDE', 'NOCHE'])->default('MAÑANA');
            $table->integer('capacidad')->default(30);
            $table->string('ubicacion', 100)->nullable();
            $table->boolean('activo')->default(true);
            
            // Relaciones
            $table->foreignId('nivel_id')->constrained('niveles')->onDelete('restrict');
            $table->foreignId('grado_id')->constrained('grados')->onDelete('restrict');
            $table->foreignId('seccion_id')->constrained('secciones')->onDelete('restrict');
            $table->foreignId('anio_academico_id')->constrained('anio_academicos')->onDelete('cascade');
            $table->foreignId('docente_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->unique(['grado_id', 'seccion_id', 'anio_academico_id'], 'unique_aula_por_anio');
            $table->index(['nivel_id', 'anio_academico_id']);
            $table->index('turno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};