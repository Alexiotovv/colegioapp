<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_avance_cuadros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id')->nullable()->index();
            $table->json('cuadros')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_avance_cuadros');
    }
};