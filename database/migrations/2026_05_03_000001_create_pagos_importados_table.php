<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_importados', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('anio_emision')->nullable()->index();
            $table->unsignedInteger('numero_fila')->nullable();
            $table->string('estudiante');
            $table->string('dni_est', 20)->nullable()->index();
            $table->string('doc_facturacion_dni', 20)->nullable()->index();
            $table->string('nombre_facturacion');
            $table->string('nivel', 50)->nullable()->index();
            $table->string('grado', 50)->nullable()->index();
            $table->string('seccion', 50)->nullable()->index();
            $table->decimal('marzo', 10, 2)->nullable();
            $table->decimal('abril', 10, 2)->nullable();
            $table->decimal('mayo', 10, 2)->nullable();
            $table->decimal('junio', 10, 2)->nullable();
            $table->decimal('julio', 10, 2)->nullable();
            $table->decimal('agosto', 10, 2)->nullable();
            $table->decimal('setiembre', 10, 2)->nullable();
            $table->decimal('octubre', 10, 2)->nullable();
            $table->decimal('noviembre', 10, 2)->nullable();
            $table->decimal('diciembre', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['estudiante', 'dni_est']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_importados');
    }
};
