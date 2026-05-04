<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_orden_merito', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->foreignId('nivel_id')->nullable()->constrained('niveles')->nullOnDelete();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['nivel_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_orden_merito');
    }
};
