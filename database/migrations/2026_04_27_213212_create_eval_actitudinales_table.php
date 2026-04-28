<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eval_actitudinales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
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
        Schema::dropIfExists('eval_actitudinales');
    }
};