<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Nota: this requires doctrine/dbal if the column already exists and needs change()
        Schema::table('carga_horaria', function (Blueprint $table) {
            $table->unsignedBigInteger('curso_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carga_horaria', function (Blueprint $table) {
            $table->unsignedBigInteger('curso_id')->nullable(false)->change();
        });
    }
};
