<?php
// database/migrations/2026_04_21_143409_create_registro_otras_evaluaciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_otras_evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('tipo_otra_evaluacion_id')->constrained('tipos_otras_evaluaciones')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            $table->string('valor', 50);
            $table->text('observacion')->nullable();
            $table->date('fecha_registro');
            $table->timestamps();
            
            // 🔥 Índices con nombres más cortos
            $table->unique(['matricula_id', 'tipo_otra_evaluacion_id', 'periodo_id'], 'unique_reg_otra_eval');
            $table->index(['periodo_id', 'tipo_otra_evaluacion_id'], 'idx_periodo_tipo_otra');
            $table->index('docente_id', 'idx_docente_otra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_otras_evaluaciones');
    }
};