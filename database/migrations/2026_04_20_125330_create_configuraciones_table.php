<?php
// database/migrations/2026_04_20_000001_create_configuraciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();
            $table->text('valor')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['texto', 'numero', 'json', 'array'])->default('texto');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar configuraciones por defecto
        DB::table('configuraciones')->insert([
            [
                'clave' => 'apreciaciones_caracteres_max',
                'valor' => '255',
                'descripcion' => 'Cantidad máxima de caracteres permitidos en las apreciaciones',
                'tipo' => 'numero',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'apreciaciones_roles_permitidos',
                'valor' => json_encode(['docente', 'tutor']),
                'descripcion' => 'Roles que pueden registrar apreciaciones',
                'tipo' => 'array',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};