<?php
// database/migrations/2026_04_13_000002_create_grados_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id'); // Primero definir columna
            $table->string('nombre', 20);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['nivel_id', 'nombre']);
            
            // Luego agregar foreign key DESPUÉS de definir la columna
            $table->foreign('nivel_id')
                  ->references('id')
                  ->on('niveles')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grados');
    }
};