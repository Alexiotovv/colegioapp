<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_avance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('competencia_id')->constrained('competencias')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            $table->string('nota', 10);
            $table->enum('tipo_calificacion', ['NUMERICA', 'LITERAL', 'CUALITATIVA'])->default('NUMERICA');
            $table->foreignId('escala_id')->nullable()->constrained('escalas_calificacion')->onDelete('set null');
            $table->enum('tipo_evaluacion', ['BIMESTRAL', 'RECUPERACION', 'SUSTITUTORIO'])->default('BIMESTRAL');
            $table->date('fecha_registro');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['matricula_id', 'competencia_id', 'periodo_id'], 'unique_nota_avance');
            $table->index(['matricula_id', 'periodo_id']);
            $table->index('docente_id');
            $table->index('nota');
            $table->index('tipo_calificacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_avance');
    }
};