<?php
// database/migrations/2026_04_13_031310_create_notas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('competencia_id')->constrained('competencias')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            
            // 🔥 CAMPO NOTA MODIFICADO - Soporta múltiples formatos
            $table->string('nota', 10);  // VARCHAR(10) para AD, A, B, C, 20, 15.5, CND, etc.
            
            // Tipo de calificación
            $table->enum('tipo_calificacion', ['NUMERICA', 'LITERAL', 'CUALITATIVA'])
                  ->default('NUMERICA');
            
            // Tabla de referencia para calificaciones literales predefinidas
            $table->foreignId('escala_id')
                  ->nullable()
                  ->constrained('escalas_calificacion')
                  ->onDelete('set null');
            
            $table->enum('tipo_evaluacion', ['BIMESTRAL', 'RECUPERACION', 'SUSTITUTORIO'])
                  ->default('BIMESTRAL');
            $table->date('fecha_registro');
            $table->text('observacion')->nullable();
            $table->timestamps();
            
            // Índices
            $table->unique(
                ['matricula_id', 'competencia_id', 'periodo_id'], 
                'unique_nota'
            );
            $table->index(['matricula_id', 'periodo_id']);
            $table->index('docente_id');
            $table->index('nota');  // Índice para búsquedas por nota
            $table->index('tipo_calificacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};