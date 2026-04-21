<?php
// database/migrations/2025_01_01_000004_create_anio_academicos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anio_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('anio', 4)->unique(); // 2024, 2025
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anio_academicos');
    }
};