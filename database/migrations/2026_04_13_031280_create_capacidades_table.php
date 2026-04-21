<?php
// database/migrations/2025_01_01_000012_create_capacidades_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competencia_id')->constrained('competencias')->onDelete('cascade');
            $table->string('nombre', 250);
            $table->text('descripcion')->nullable();
            $table->decimal('ponderacion', 5, 2)->default(100.00); // % dentro de la competencia
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacidades');
    }
};