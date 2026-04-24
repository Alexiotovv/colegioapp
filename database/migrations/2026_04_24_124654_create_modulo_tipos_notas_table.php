<?php
// database/migrations/2026_04_24_124654_create_modulo_tipos_notas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulo_tipos_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos_registro')->onDelete('cascade');
            $table->foreignId('tipo_nota_id')->constrained('tipos_notas')->onDelete('cascade');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['modulo_id', 'tipo_nota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_tipos_notas');
    }
};