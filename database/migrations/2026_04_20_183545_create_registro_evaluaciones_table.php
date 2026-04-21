<?php
// database/migrations/2026_04_20_000011_create_registro_evaluaciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('evaluacion_id')->constrained('evaluaciones')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            $table->enum('valoracion', ['SIEMPRE', 'CASI SIEMPRE', 'ALGUNAS VECES', 'NUNCA'])->default('SIEMPRE');
            $table->text('comentario')->nullable();
            $table->date('fecha_registro');
            $table->timestamps();
            
            $table->unique(['matricula_id', 'evaluacion_id', 'periodo_id'], 'unique_registro_evaluacion');
            $table->index(['periodo_id', 'evaluacion_id']);
            $table->index('docente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_evaluaciones');
    }
};