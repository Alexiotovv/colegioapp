<?php
// database/migrations/2025_01_01_000007_create_apoderados_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apoderados', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->unique();
            $table->string('nombres', 60);
            $table->string('apellido_paterno', 60);
            $table->string('apellido_materno', 60);
            $table->string('direccion', 100);
            $table->enum('sexo', ['M', 'F']);
            $table->string('telefono', 20);
            $table->string('email', 100);
            $table->enum('parentesco', ['PADRE', 'MADRE', 'TUTOR', 'HERMANO', 'OTRO'])->default('TUTOR');
            $table->boolean('recibe_notificaciones')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apoderados');
    }
};