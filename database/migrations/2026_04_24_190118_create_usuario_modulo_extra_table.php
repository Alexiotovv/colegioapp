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
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_modulo_extra');
    }
};