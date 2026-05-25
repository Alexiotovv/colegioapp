<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modulos')) {
            return;
        }

        $now = now();

        DB::table('modulos')->updateOrInsert(
            ['codigo' => 'exportar-por-alumno'],
            [
                'nombre' => 'Exportar Libreta por Alumno',
                'ruta' => 'admin.libretas.exportar-por-alumno.index',
                'icono' => 'fa-user-graduate',
                'orden' => 72,
                'padre_id' => null,
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        if (!Schema::hasTable('rol_modulo')) {
            return;
        }

        $moduloId = DB::table('modulos')->where('codigo', 'exportar-por-alumno')->value('id');
        $moduloLibretasId = DB::table('modulos')->where('codigo', 'libretas')->value('id');

        if (!$moduloId || !$moduloLibretasId) {
            return;
        }

        // Replica accesos de roles que ya tienen el módulo libretas.
        $rolesConLibretas = DB::table('rol_modulo')
            ->where('modulo_id', $moduloLibretasId)
            ->where('activo', true)
            ->pluck('rol_id');

        foreach ($rolesConLibretas as $rolId) {
            DB::table('rol_modulo')->updateOrInsert(
                ['rol_id' => $rolId, 'modulo_id' => $moduloId],
                ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        if (!Schema::hasTable('usuario_modulo_extra')) {
            return;
        }

        // Replica accesos extras de usuarios que ya tienen libretas.
        $usuariosConLibretasExtra = DB::table('usuario_modulo_extra')
            ->where('modulo_id', $moduloLibretasId)
            ->where('activo', true)
            ->pluck('usuario_id');

        foreach ($usuariosConLibretasExtra as $usuarioId) {
            DB::table('usuario_modulo_extra')->updateOrInsert(
                ['usuario_id' => $usuarioId, 'modulo_id' => $moduloId],
                ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('modulos')) {
            return;
        }

        $moduloId = DB::table('modulos')->where('codigo', 'exportar-por-alumno')->value('id');

        if (!$moduloId) {
            return;
        }

        if (Schema::hasTable('rol_modulo')) {
            DB::table('rol_modulo')->where('modulo_id', $moduloId)->delete();
        }

        if (Schema::hasTable('usuario_modulo_extra')) {
            DB::table('usuario_modulo_extra')->where('modulo_id', $moduloId)->delete();
        }

        DB::table('modulos')->where('id', $moduloId)->delete();
    }
};
