<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodos', function (Blueprint $table) {
            $table->boolean('avance_activo')->default(false)->after('activo');
        });

        if (Schema::hasTable('avance_registro_nota_habilitaciones')) {
            DB::table('periodos')
                ->leftJoin('avance_registro_nota_habilitaciones as h', 'h.periodo_id', '=', 'periodos.id')
                ->update([
                    'periodos.avance_activo' => DB::raw('COALESCE(h.habilitado, 0)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('periodos', function (Blueprint $table) {
            $table->dropColumn('avance_activo');
        });
    }
};
