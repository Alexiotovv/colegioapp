<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('descripcion_cuadros_dinamicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuadro_id')->index();
            $table->text('texto')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('descripcion_cuadros_dinamicos');
    }
};
