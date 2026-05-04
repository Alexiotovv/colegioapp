<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('registro_orden_meritos')) {
            return;
        }

        Schema::table('registro_orden_meritos', function (Blueprint $table) {
            $table->integer('nota_valor')->nullable()->after('tipo_orden_merito_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('registro_orden_meritos')) {
            return;
        }

        Schema::table('registro_orden_meritos', function (Blueprint $table) {
            $table->dropColumn('nota_valor');
        });
    }
};
