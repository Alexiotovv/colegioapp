<?php
// database/migrations/2025_01_01_000014_create_pagos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->string('mes', 20); // Enero, Febrero, etc.
            $table->integer('numero_mes'); // 1,2,3...12
            $table->string('concepto', 100); // Pensión, Matrícula, Materiales
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto_pagado', 10, 2)->nullable();
            $table->decimal('mora', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'pagado', 'vencido', 'parcial'])->default('pendiente');
            $table->string('comprobante', 50)->nullable();
            $table->string('numero_operacion', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['matricula_id', 'estado']);
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};