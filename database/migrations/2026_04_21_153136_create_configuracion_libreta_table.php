<?php
// database/migrations/2026_04_21_000021_create_configuracion_libreta_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_libreta', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200)->default('Libreta de Notas');
            $table->string('subtitulo', 200)->nullable();
            $table->string('dre', 100)->nullable(); // Dirección Regional de Educación
            $table->string('ugel', 100)->nullable(); // Unidad de Gestión Educativa Local
            $table->string('logo_pais')->nullable(); // ruta del logo del país
            $table->string('logo_region')->nullable(); // ruta del logo de la región
            $table->string('logo_institucion')->nullable(); // ruta del logo de la institución
            $table->string('firma_director')->nullable(); // ruta de la firma del director
            $table->string('nombre_director', 200)->nullable();
            $table->string('cargo_director', 100)->nullable();
            $table->string('firma_tutor')->nullable(); // ruta de la firma del tutor
            $table->string('nombre_tutor', 200)->nullable();
            $table->string('cargo_tutor', 100)->nullable();
            $table->text('texto_pie')->nullable(); // texto adicional al pie
            $table->boolean('mostrar_en_libreta')->default(true);
            $table->string('firma_subdirector')->nullable();
            $table->string('nombre_subdirector', 200)->nullable();
            $table->string('cargo_subdirector', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_libreta');
    }
};