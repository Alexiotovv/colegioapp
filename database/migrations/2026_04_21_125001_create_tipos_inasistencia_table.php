<?php
// database/migrations/2026_04_21_000001_create_tipos_inasistencia_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_inasistencia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
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
        Schema::dropIfExists('tipos_inasistencia');
    }
};