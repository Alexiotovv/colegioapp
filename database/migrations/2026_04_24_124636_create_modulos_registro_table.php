<?php
// database/migrations/2026_04_24_000002_create_modulos_registro_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulos_registro', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('ruta', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar módulos por defecto
        DB::table('modulos_registro')->insert([
            ['codigo' => 'notas', 'nombre' => 'Registro de Notas', 'ruta' => 'notas.index', 'created_at' => now()],
            ['codigo' => 'competencias_transversales', 'nombre' => 'Competencias Transversales', 'ruta' => 'registro-competencias-transversales.index', 'created_at' => now()],
            ['codigo' => 'evaluaciones_padre', 'nombre' => 'Evaluación del Padre', 'ruta' => 'registro-evaluaciones.index', 'created_at' => now()],
            ['codigo' => 'otras_evaluaciones', 'nombre' => 'Otras Evaluaciones', 'ruta' => 'registro-otras-evaluaciones.index', 'created_at' => now()],
            ['codigo' => 'inasistencias', 'nombre' => 'Inasistencias', 'ruta' => 'registro-asistencias.index', 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos_registro');
    }
};