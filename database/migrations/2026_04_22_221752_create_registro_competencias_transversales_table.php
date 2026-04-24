<?php
// database/migrations/2026_04_22_221752_create_registro_competencias_transversales_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_competencias_transversales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            
            // 🔥 Foreign key con nombre más corto
            $table->foreignId('competencia_transversal_id')->constrained(
                table: 'competencias_transversales',
                indexName: 'fk_reg_ct_ct_id'  // Nombre corto para la foreign key
            )->onDelete('cascade');
            
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            $table->string('nota', 10);
            $table->enum('tipo_calificacion', ['NUMERICA', 'LITERAL'])->default('LITERAL');
            $table->text('conclusion')->nullable();
            $table->date('fecha_registro');
            $table->timestamps();
            
            // Índices con nombres más cortos
            $table->unique(['matricula_id', 'competencia_transversal_id', 'periodo_id'], 'unique_reg_ct');
            $table->index(['periodo_id', 'competencia_transversal_id'], 'idx_periodo_ct');
            $table->index('docente_id', 'idx_docente_ct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_competencias_transversales');
    }
};