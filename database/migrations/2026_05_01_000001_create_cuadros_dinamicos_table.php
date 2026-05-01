<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cuadros_dinamicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->nullable()->unique();
            $table->unsignedBigInteger('nivel_id')->nullable()->index();
            $table->string('tipo')->nullable(); // ej: sin_evaluaciones, tabla_notas, leyenda, etc.
            $table->string('nota_tipo')->nullable(); // numeric, literal, none
            $table->boolean('involucra_libreta')->default(false);
            $table->string('ancho')->default('col-12'); // col-12 or col-6
            $table->boolean('mostrar_en_libreta')->default(true);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->json('opciones')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuadros_dinamicos');
    }
};
