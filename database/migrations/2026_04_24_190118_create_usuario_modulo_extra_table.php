<?php
// database/migrations/2026_04_24_000012_create_usuario_modulo_extra_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_modulo_extra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('modulo_id')->constrained('modulos')->onDelete('cascade');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['usuario_id', 'modulo_id']);
        });


        // Después de insertar los módulos, asignar TODOS los módulos al rol admin
        $rolAdmin = DB::table('roles')->where('nombre', 'admin')->first();
        if ($rolAdmin) {
            $modulos = DB::table('modulos')->pluck('id');
            foreach ($modulos as $moduloId) {
                DB::table('rol_modulo')->updateOrInsert(
                    ['rol_id' => $rolAdmin->id, 'modulo_id' => $moduloId],
                    ['activo' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Asignar TODOS los módulos al usuario admin específico
        $usuarioAdmin = DB::table('users')->where('email', 'admin@colcoopcv.edu.pe')->first();
        if ($usuarioAdmin) {
            $modulos = DB::table('modulos')->pluck('id');
            foreach ($modulos as $moduloId) {
                DB::table('usuario_modulo_extra')->updateOrInsert(
                    ['usuario_id' => $usuarioAdmin->id, 'modulo_id' => $moduloId],
                    ['activo' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_modulo_extra');
    }
};