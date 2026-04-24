<?php
// database/migrations/2026_04_24_000001_create_tipos_notas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_notas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            $table->enum('tipo_dato', ['NUMERICO', 'LITERAL'])->default('LITERAL');
            $table->decimal('valor_numerico', 5, 2)->nullable(); // para ordenamiento
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar tipos de notas por defecto
        DB::table('tipos_notas')->insert([
            ['codigo' => 'AD', 'nombre' => 'Logro Destacado', 'tipo_dato' => 'LITERAL', 'valor_numerico' => 18, 'orden' => 1],
            ['codigo' => 'A', 'nombre' => 'Logro Esperado', 'tipo_dato' => 'LITERAL', 'valor_numerico' => 14, 'orden' => 2],
            ['codigo' => 'B', 'nombre' => 'En Proceso', 'tipo_dato' => 'LITERAL', 'valor_numerico' => 11, 'orden' => 3],
            ['codigo' => 'C', 'nombre' => 'En Inicio', 'tipo_dato' => 'LITERAL', 'valor_numerico' => 0, 'orden' => 4],
            ['codigo' => 'CND', 'nombre' => 'Competencia No Desarrollada', 'tipo_dato' => 'LITERAL', 'valor_numerico' => null, 'orden' => 5],
            ['codigo' => 'ND', 'nombre' => 'No Desarrollado', 'tipo_dato' => 'LITERAL', 'valor_numerico' => null, 'orden' => 6],
            ['codigo' => 'EXO', 'nombre' => 'Exonerado', 'tipo_dato' => 'LITERAL', 'valor_numerico' => null, 'orden' => 7],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_notas');
    }
};