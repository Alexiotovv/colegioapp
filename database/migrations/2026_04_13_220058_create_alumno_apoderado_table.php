<?php
// database/migrations/2026_04_13_000000_create_alumno_apoderado_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_apoderado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('apoderado_id')->constrained('apoderados')->onDelete('cascade');
            $table->string('parentesco', 50)->nullable();
            $table->boolean('recibe_notificaciones')->default(true);
            $table->boolean('es_tutor')->default(false);
            $table->boolean('puede_retirar')->default(false);
            $table->timestamps();
            
            // Índices
            $table->unique(['alumno_id', 'apoderado_id']);
            $table->index('alumno_id');
            $table->index('apoderado_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_apoderado');
    }
};