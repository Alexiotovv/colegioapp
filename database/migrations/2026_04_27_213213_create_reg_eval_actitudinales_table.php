<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reg_eval_actitudinales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('eval_actitudinal_id')->constrained('eval_actitudinales')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('users')->onDelete('restrict');
            $table->string('valoracion', 255);
            $table->text('comentario')->nullable();
            $table->date('fecha_registro');
            $table->timestamps();
            
            $table->unique(['matricula_id', 'eval_actitudinal_id', 'periodo_id'], 'unique_reg_act');
            $table->index(['periodo_id', 'eval_actitudinal_id'], 'idx_periodo_act');
            $table->index('docente_id', 'idx_docente_act');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reg_eval_actitudinales');
    }
};  