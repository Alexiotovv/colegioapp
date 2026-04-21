<?php
// database/migrations/2026_04_20_000002_create_apreciaciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apreciaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            $table->text('apreciacion');
            $table->date('fecha_registro');
            $table->timestamps();
            
            // Índices
            $table->unique(['matricula_id', 'periodo_id'], 'unique_apreciacion');
            $table->index(['periodo_id']);
            $table->index('docente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apreciaciones');
    }
};