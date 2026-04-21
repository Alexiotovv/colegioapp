<?php
// database/migrations/2025_01_01_000011_create_competencias_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('grado_id')->nullable()->constrained('grados')->onDelete('cascade');
            $table->string('nombre', 250);
            $table->text('descripcion')->nullable();
            $table->decimal('ponderacion', 5, 2)->default(100.00); // % dentro del curso
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['curso_id', 'grado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competencias');
    }
};