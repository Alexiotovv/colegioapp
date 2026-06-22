<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avance_registro_nota_habilitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->boolean('habilitado')->default(false);
            $table->timestamps();

            $table->unique('periodo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avance_registro_nota_habilitaciones');
    }
};