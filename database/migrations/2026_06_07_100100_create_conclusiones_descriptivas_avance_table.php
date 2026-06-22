<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conclusiones_descriptivas_avance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_avance_id')->constrained('notas_avance')->onDelete('cascade');
            $table->text('conclusion');
            $table->timestamps();

            $table->unique('nota_avance_id');
            $table->index('nota_avance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conclusiones_descriptivas_avance');
    }
};