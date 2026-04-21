<?php
// database/migrations/2026_04_21_000020_create_configuracion_institucion_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_institucion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('ruc', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('telefono2', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('logo_login')->nullable(); // ruta del logo para login
            $table->string('logo_dashboard')->nullable(); // ruta del logo para dashboard
            $table->string('favicon')->nullable(); // ruta del favicon
            $table->text('descripcion')->nullable();
            $table->string('web', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_institucion');
    }
};