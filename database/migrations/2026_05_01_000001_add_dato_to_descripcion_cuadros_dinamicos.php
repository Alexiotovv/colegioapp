<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('descripcion_cuadros_dinamicos')) {
            Schema::table('descripcion_cuadros_dinamicos', function (Blueprint $table) {
                if (!Schema::hasColumn('descripcion_cuadros_dinamicos', 'dato')) {
                    $table->text('dato')->nullable()->after('texto');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('descripcion_cuadros_dinamicos')) {
            Schema::table('descripcion_cuadros_dinamicos', function (Blueprint $table) {
                if (Schema::hasColumn('descripcion_cuadros_dinamicos', 'dato')) {
                    $table->dropColumn('dato');
                }
            });
        }
    }
};
