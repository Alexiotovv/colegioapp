<?php
// database/migrations/2026_04_13_031309_create_escalas_calificacion_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalas_calificacion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();  // AD, A, B, C, CND, etc.
            $table->string('nombre', 50);            // "Logro Destacado", "Logro Esperado", etc.
            $table->text('descripcion')->nullable();  // Descripción detallada
            $table->decimal('valor_numerico_min', 5, 2)->nullable();  // Rango mínimo (opcional)
            $table->decimal('valor_numerico_max', 5, 2)->nullable();  // Rango máximo (opcional)
            $table->enum('nivel', ['PRIMARIA', 'SECUNDARIA', 'AMBOS'])->default('AMBOS');
            $table->boolean('aprobatorio')->default(false);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalas_calificacion');
    }
};