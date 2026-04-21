<?php
// database/migrations/2025_01_01_000003_create_secciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 2); // A, B, C, D
            $table->string('turno', 20)->default('MAÑANA'); // MAÑANA, TARDE
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secciones');
    }
};