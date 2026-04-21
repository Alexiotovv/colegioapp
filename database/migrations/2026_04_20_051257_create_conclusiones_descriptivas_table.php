<?php
// database/migrations/2026_04_20_000001_create_conclusiones_descriptivas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conclusiones_descriptivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_id')->constrained('notas')->onDelete('cascade');
            $table->text('conclusion');
            $table->timestamps();
            
            $table->index('nota_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conclusiones_descriptivas');
    }
};