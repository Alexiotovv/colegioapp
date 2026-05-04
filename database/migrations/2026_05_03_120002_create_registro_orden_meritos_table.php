<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_orden_meritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('tipo_orden_merito_id')->constrained('tipos_orden_merito')->onDelete('restrict');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            $table->text('observacion')->nullable();
            $table->date('fecha_registro');
            $table->timestamps();

            $table->unique(['matricula_id', 'periodo_id'], 'unique_registro_orden_merito');
            $table->index(['periodo_id', 'tipo_orden_merito_id']);
            $table->index('docente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_orden_meritos');
    }
};
