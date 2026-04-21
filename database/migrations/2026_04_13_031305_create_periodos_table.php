<?php
// database/migrations/2025_01_01_000005_create_periodos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_academico_id')->constrained('anio_academicos')->onDelete('cascade');
            $table->string('nombre', 20); // I Bimestre, II Bimestre, etc.
            $table->integer('orden'); // 1,2,3,4
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['anio_academico_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};