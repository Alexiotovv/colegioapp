<?php
// database/migrations/2026_04_21_000010_create_tipos_otras_evaluaciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_otras_evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->enum('tipo_dato', ['NUMERICO', 'LITERAL'])->default('NUMERICO');
            $table->integer('min_valor')->nullable();
            $table->integer('max_valor')->nullable();
            $table->text('opciones_literales')->nullable(); // JSON: ["AD","A","B","C","ND"]
            $table->foreignId('nivel_id')->constrained('niveles')->onDelete('cascade');
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['nivel_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_otras_evaluaciones');
    }
};