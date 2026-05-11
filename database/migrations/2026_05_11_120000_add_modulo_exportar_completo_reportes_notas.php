<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('modulos')) {
            return;
        }

        $now = now();

        \Illuminate\Support\Facades\DB::table('modulos')->updateOrInsert(
            ['codigo' => 'reportes-notas-exportar-completo'],
            [
                'nombre' => 'Reporte de Notas Completo',
                'ruta' => null,
                'icono' => 'fa-file-excel',
                'orden' => 71,
                'padre_id' => null,
                'activo' => 1,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $adminRole = \Illuminate\Support\Facades\DB::table('roles')->where('nombre', 'admin')->first();
        $moduloId = \Illuminate\Support\Facades\DB::table('modulos')
            ->where('codigo', 'reportes-notas-exportar-completo')
            ->value('id');

        if ($adminRole && $moduloId && \Illuminate\Support\Facades\Schema::hasTable('rol_modulo')) {
            \Illuminate\Support\Facades\DB::table('rol_modulo')->updateOrInsert(
                ['rol_id' => $adminRole->id, 'modulo_id' => $moduloId],
                ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('modulos')) {
            return;
        }

        $moduloId = \Illuminate\Support\Facades\DB::table('modulos')
            ->where('codigo', 'reportes-notas-exportar-completo')
            ->value('id');

        if ($moduloId && \Illuminate\Support\Facades\Schema::hasTable('rol_modulo')) {
            \Illuminate\Support\Facades\DB::table('rol_modulo')->where('modulo_id', $moduloId)->delete();
        }

        if ($moduloId && \Illuminate\Support\Facades\Schema::hasTable('usuario_modulo_extra')) {
            \Illuminate\Support\Facades\DB::table('usuario_modulo_extra')->where('modulo_id', $moduloId)->delete();
        }

        \Illuminate\Support\Facades\DB::table('modulos')->where('codigo', 'reportes-notas-exportar-completo')->delete();
    }
};